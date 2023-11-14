<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Workflow\WorkflowInterface;

class BlogPostCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly WorkflowInterface $blogPostStateMachine,
    ) {}

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Blogpost')
            ->setEntityLabelInPlural('Blogposts')
            ->setSearchFields(['title', 'state'])
            ->setDefaultSort(['creationDate' => 'DESC'])
            ;
    }

    public static function getEntityFqcn(): string
    {
        return BlogPost::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $currentBlogPost = $this->getContext()->getEntity()->getInstance();
        /* @var BlogPost $currentBlogPost */

        yield TextField::new('title');
        yield TextEditorField::new('content')
            ->hideOnIndex();

        $states = BlogPost::STATE_OPTIONS;
        if(!is_null($currentBlogPost)) {
            foreach($states as $stateKey => $stateLabel) {
                if(
                    !$this->blogPostStateMachine->can($currentBlogPost, $stateKey) &&
                    ($stateKey !== $currentBlogPost->getState())
                ) {
                    unset($states[$stateKey]);
                }
            }
        }

        if(Crud::PAGE_NEW !== $pageName) {
            yield ChoiceField::new('state')->setChoices($states)->setDisabled(count($states) <= 1);

            yield DateTimeField::new('creationDate')->setFormTypeOptions([
                'years' => range(date('Y'), ((int) date('Y') )+ 5),
                'widget' => 'single_text',
            ])->setFormTypeOption('disabled', (Crud::PAGE_EDIT === $pageName));
        }
    }
}