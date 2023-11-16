<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Message\CommentMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConferenceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/')]
    public function indexNoLocale(): Response
    {
       return $this->redirectToRoute('homepage', ['_locale' => 'en']);
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ])->setSharedMaxAge(3600);
    }

    #[Route('/{_locale<%app.supported_locales%>}/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        CommentRepository $commentRepository,
        NotifierInterface $notifier,
        #[Autowire('%photo_dir%')] string $photoDir,
    ): Response {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                $photo->move($photoDir, $filename);
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            $this->entityManager->flush();
            $reviewUrl = $this->generateUrl('review_comment', ['id' => $comment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
             $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl, $context));
            $notifier->send(new Notification($this->translator->trans('form.comment.submitted'), ['browser']));
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        if ($form->isSubmitted()) {
            $notifier->send(new Notification($this->translator->trans('form.comment.invalid'), ['browser']));
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/conference_header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/header.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ])->setSharedMaxAge(3600);;
    }

}
