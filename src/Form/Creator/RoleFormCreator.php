<?php
/**
 * This file contains a form creator for Roles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use BZIon\Form\Type\IpType;
use BZIon\Form\Type\ModelType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for roles
 */
class RoleFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        /** @var \Role $role */
        $role = $this->editing;

        return $builder
            ->add('name', 'text', array(
                'constraints' => new NotBlank(),
            ))
            ->add('display_as_leader', 'checkbox', array(
                'required' => false
            ))
            ->add('display_icon', 'text', array(
                'required' => false
            ))
            ->add('display_color', 'text', array(
                'constraints' => new NotBlank(),
                'empty_data' => 'green'
            ))
            ->add('display_name', 'text', array(
                'required' => false
            ))
            ->add('display_order', 'number', array(
                'empty_data' => 0
            ))
            ->add('permissions', new ModelType('Permission', false), array(
                'multiple' => true,
                'required' => false
            ))
            ->add('enter', 'submit');
    }

    /**
     * {@inheritdoc}
     * @param \Role $role
     */
    public function fill($form, $role)
    {
        $form->get('name')->setData($role->getName());
        $form->get('display_as_leader')->setData($role->displayAsLeader());
        $form->get('display_icon')->setData($role->getDisplayIcon());
        $form->get('display_color')->setData($role->getDisplayColor());
        $form->get('display_name')->setData($role->getDisplayName());
        $form->get('display_order')->setData($role->getDisplayOrder());
        $form->get('permissions')->setData($role->getPermObjects());
    }

    /**
     * {@inheritdoc}
     * @param \Role $role
     */
    public function update($form, $role)
    {
        $role->setName($form->get('name')->getData())
            ->setDisplayAsLeader($form->get('display_as_leader')->getData())
            ->setDisplayIcon($form->get('display_icon')->getData())
            ->setDisplayColor($form->get('display_color')->getData())
            ->setDisplayName($form->get('display_name')->getData())
            ->setDisplayOrder($form->get('display_order')->getData())
            ->setPerms($form->get('permissions')->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $role = \Role::createNewRole(
            $form->get('name')->getData(),
            true,
            $form->get('display_as_leader')->getData(),
            $form->get('display_icon')->getData(),
            $form->get('display_color')->getData(),
            $form->get('display_name')->getData(),
            $form->get('display_order')->getData()
        );

        $role->setPerms($form->get('permissions')->getData());

        return $role;
    }
}
