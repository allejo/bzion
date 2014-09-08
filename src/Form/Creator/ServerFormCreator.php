<?php
/**
 * This file contains a form creator for Servers
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\PlayerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form creator for servers
 */
class ServerFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('address', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 50,
                    )),
                ),
            ))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 100,
                    )),
                ),
            ))
            ->add('owner', new PlayerType())
            ->add('enter', 'submit');
    }

    /**
     * {@inheritDoc}
     */
    public function fill($form, $server)
    {
        $form->get('name')->setData($server->getName());
        $form->get('address')->setData($server->getAddress());
        $form->get('owner')->get('players')->setData($server->getOwner());
    }

    /**
     * {@inheritDoc}
     */
    public function update($form, $server)
    {
        $server->setName($form->get('name')->getData())
               ->setAddress($form->get('address')->getData())
               ->setOwner($form->get('owner')->getData()->getId())
               ->forceUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function enter($form)
    {
        return \Server::addServer(
            $form->get('name')->getData(),
            $form->get('address')->getData(),
            1,
            $form->get('owner')->getData()->getId()
        );
    }
}
