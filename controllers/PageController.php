<?php

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PageController extends CRUDController
{
    public function showDefaultAction()
    {
        $page = Page::getHomePage();

        if ($page->isValid())
            return $this->forward("show", array("page" => $page));

        return $this->render("Page/default.html.twig");
    }

    public function showAction(Page $page)
    {
        return array("page" => $page);
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Page $page)
    {
        return $this->edit($page, $me, "page");
    }

    public function deleteAction(Player $me, Page $page)
    {
        return $this->delete($page, $me);
    }

    protected function fill($form, $page)
    {
        $form->get('name')->setData($page->getName());
        $form->get('content')->setData($page->getContent());
        $form->get('status')->setData($page->getStatus());
    }

    protected function update($form, $page, $me)
    {
        $page->setName($form->get('name')->getData());
        $page->setContent($form->get('content')->getData());
        $page->setStatus($form->get('status')->getData());
        $page->updateEditTimestamp();

        return $page;
    }

    protected function enter($form, $me)
    {
        return Page::addPage(
            $form->get('name')->getData(),
            $form->get('content')->getData(),
            $me->getId(),
            $form->get('status')->getData()
        );
    }

    protected function createForm()
    {
        return Service::getFormFactory()->createBuilder()
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 32,
                    )),
                ),
            ))
            ->add('content', 'textarea', array(
                'constraints' => new NotBlank()
            ))
            ->add('status', 'choice', array(
                'choices' => array(
                    'live' => 'Public',
                    'revision' => 'Revision',
                    'disabled' => 'Disabled',
                ),
            ))
            ->add('enter', 'submit')
            ->getForm();
    }

    protected function redirectToList($model)
    {
        return new RedirectResponse(
            Service::getGenerator()->generate("index")
        );
    }
}
