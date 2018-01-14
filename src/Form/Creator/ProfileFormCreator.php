<?php
/**
 * This file contains a form creator for player profiles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\ModelType;
use BZIon\Form\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator to allow users to edit their profiles
 *
 * @property \Player $editing
 */
class ProfileFormCreator extends ModelFormCreator
{
    /**
     * True if the player is editing their own profile, false if an admin is
     * editing the player
     *
     * @var bool
     */
    private $editingSelf = true;

    /**
     * Set whether the player is editing their own profile
     *
     * @param bool $editingSelf True if the player is editing their own
     *                             profile, false if an admin is editing the
     *                             player
     */
    public function setEditingSelf($editingSelf)
    {
        $this->editingSelf = $editingSelf;
    }

    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $emailConstraints = array(new Length(array('max' => 255)));
        if (!\Service::isDebug()) {
            // Don't validate e-mails when developing, for example to allow
            // messaging anyone@localhost
            $emailConstraints[] = new Email();
        }

        $themeNames = array_column(\Service::getSiteThemes(), 'name');
        $themeSlugs = array_column(\Service::getSiteThemes(), 'slug');
        $themes = array_combine($themeSlugs, $themeNames);

        $builder
            ->add('description', TextareaType::class, array(
                'constraints' => new Length(array('max' => 8000)),
                'data'        => $this->editing->getDescription(),
                'required'    => false
            ))
            ->add('avatar', FileType::class, array(
                'constraints' => new Image(array(
                    'minWidth'  => 50,
                    'minHeight' => 50,
                    'maxSize'   => '4M'
                )),
                'required' => false
            ))
            ->add('delete_avatar', SubmitType::class)
            ->add('country', new ModelType('Country'), array(
                'constraints' => new NotBlank(),
                'data'        => $this->editing->getCountry()
            ))
            ->add('email', EmailType::class, array(
                'constraints' => $emailConstraints,
                'data'        => $this->editing->getEmailAddress(),
                'label'       => 'E-Mail Address',
                'required'    => false
            ))
            // TODO: Disable this with JS when no e-mail has been specified
            ->add('receive', ChoiceType::class, array(
                'choices' => array(
                    'nothing'    => 'Nothing',
                    'messages'   => 'Messages only',
                    'everything' => 'Everything'
                ),
                'data'        => $this->editing->getReceives(),
                'label'       => 'Receive e-mails about',
                'expanded'    => true,
                'placeholder' => false,
                'required'    => false
            ))
            ->add('timezone', new TimezoneType($this->editing->getTimezone()), array(
                'constraints' => new NotBlank(),
                'data'        => $this->editing->getTimezone()
            ))
            ->add('theme', ChoiceType::class, [
                'choices'  => $themes,
                'data'     => $this->editing->getTheme(),
                'required' => true,
            ])
            ->add('color_blind_assist', CheckboxType::class, [
                'data'     => $this->editing->hasColorBlindAssist(),
                'required' => false,
                'label'    => 'Color Blind Assistance',
                'label_attr' => [
                    'data-beta' => true,
                ],
                'attr' => [
                    'data-help-message' => 'When checked, actions denoted by color are modified to help color blindness.',
                ],
            ])
        ;

        if (!$this->editingSelf) {
            $builder->add('roles', new ModelType('Role', false), array(
                'constraints' => new NotBlank(),
                'data'        => \Role::getRoles($this->editing->getId()),
                'multiple'    => true
            ));
        }

        $builder->add('enter', SubmitType::class, [
            'label' => 'Save Profile',
            'attr' => [
                'class' => 'c-button--blue pattern--upward-stripes',
            ],
        ]);

        $address = $this->editing->getEmailAddress();
        if (!$this->editingSelf && !empty($address) && !$this->editing->isVerified()) {
            // Show a button to verify an unverified user's e-mail address to
            // admins
            $builder->add('verify_email', 'submit', array(
                'attr' => array(
                    'class' => 'c-button--grey'
                ),
                'label' => 'Verify E-Mail address'
            ));
        }

        return $builder;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Player $player
     */
    public function update($form, $player)
    {
        $player->setDescription($form->get('description')->getData());
        $player->setTimezone($form->get('timezone')->getData());
        $player->setCountry($form->get('country')->getData()->getId());
        $player->setReceives($form->get('receive')->getData());
        $player->setTheme($form->get('theme')->getData());
        $player->setColorBlindAssist($form->get('color_blind_assist')->getData());

        if ($form->get('delete_avatar')->isClicked()) {
            $player->resetAvatar();
        } else {
            $player->setAvatarFile($form->get('avatar')->getData());
        }

        if (!$this->editingSelf) {
            $player->setRoles($form->get('roles')->getData());
        }
    }
}
