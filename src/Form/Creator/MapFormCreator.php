<?php
/**
 * This file contains a form creator for Teams
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Constraint\UniqueAlias;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for teams
 */
class MapFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $builder
            ->add('name', TextType::class, array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'min' => 2,
                        'max' => 40,
                    ))
                )
            ))
            ->add('alias', TextType::class, array(
                'constraints' => array(
                   new Length(array(
                        'max' => 40,
                    )),
                    new UniqueAlias('Map', $this->editing)
                ),
                'required' => false
            ))
            ->add('description', TextareaType::class, array(
                'required' => false
            ))
            ->add('avatar', FileType::class, array(
                'constraints' => new Image(array(
                    'minWidth'  => 60,
                    'minHeight' => 60,
                    'maxSize'   => '8M'
                )),
                'required' => false
            ))
            ->add('shot_count', IntegerType::class, [
                'label'    => 'Max shot count',
                'required' => true,
            ])
            ->add('jumping', CheckboxType::class, [
                'label'    => 'Map allows jumping',
                'required' => false,
            ])
            ->add('ricochet', CheckboxType::class, [
                'label'    => 'Map allows ricochet',
                'required' => false,
            ])
        ;

        if ($this->editing && $this->editing->getAvatar() !== null) {
            // We are editing the map, not creating it
            $builder->add('delete_image', 'submit');
        }

        return $builder->add('submit', 'submit');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Map $map
     */
    public function fill($form, $map)
    {
        $form->get('name')->setData($map->getName());
        $form->get('alias')->setData($map->getAlias());
        $form->get('description')->setData($map->getDescription());
        $form->get('shot_count')->setData($map->getShotCount());
        $form->get('jumping')->setData($map->isJumpingEnabled());
        $form->get('ricochet')->setData($map->isRicochetEnabled());
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return
            \Map::addMap(
                $form->get('name')->getData(),
                $form->get('alias')->getData(),
                $form->get('description')->getData()
            )
            ->setAvatarFile($form->get('avatar')->getData())
            ->setShotCount($form->get('shot_count')->getData())
            ->setJumpingEnabled($form->get('jumping')->getData())
            ->setRicochetEnabled($form->get('ricochet')->getData())
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Map $map
     */
    public function update($form, $map)
    {
        $map->setName($form->get('name')->getData());
        $map->setAlias($form->get('alias')->getData());
        $map->setDescription($form->get('description')->getData());
        $map->setShotCount($form->get('shot_count')->getData());
        $map->setJumpingEnabled($form->get('jumping')->getData());
        $map->setRicochetEnabled($form->get('ricochet')->getData());

        if ($form->has('delete_image') && $form->get('delete_image')->isClicked()) {
            $map->setAvatar(null);
        } else {
            $map->setAvatarFile($form->get('avatar')->getData());
        }

        return $map;
    }
}
