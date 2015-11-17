<?php
/**
 * This file contains a form creator for Bans
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use BZIon\Form\Type\IpType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for bans
 */
class BanFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('player', new AdvancedModelType(array('player', 'team')), array(
                'constraints' => new NotBlank(),
                'disabled'    => $this->isEdit(),
                'data'        => $this->editing
            ))
            ->add(
                $builder->create('automatic_expiration', 'checkbox', array(
                    'data'     => true,
                    'required' => false,
                ))->setDataLocked(false) // Don't lock the data so we can change
                                         // the default value later if needed
            )
            ->add(
                $builder->create('expiration', 'datetime', array(
                    'data' => \TimeDate::now(),
                ))->setDataLocked(false)
            )
            ->add('reason', 'textarea', array(
                'constraints' => new NotBlank(),
                'required'    => true
            ))
            ->add('server_join_allowed', 'checkbox', array(
                'data'     => true,
                'required' => false,
            ))
            ->add('server_message', 'text', array(
                'required'    => false,
                'constraints' => new Length(array(
                    'max' => 150,
                ))
            ))
            ->add('ip_addresses', new IpType(), array(
                'required' => false,
            ))
            ->add('enter', 'submit')
            ->setDataLocked(false);
    }

    /**
     * {@inheritdoc}
     */
    public function fill($form, $ban)
    {
        $form->get('player')->setData($ban->getVictim());
        $form->get('reason')->setData($ban->getReason());
        $form->get('server_message')->setData($ban->getServerMessage());
        $form->get('server_join_allowed')->setData($ban->allowedServerJoin());
        $form->get('ip_addresses')->setData($ban->getIpAddresses());

        if ($ban->willExpire()) {
            $form->get('expiration')->setData($ban->getExpiration());
        } else {
            $form->get('automatic_expiration')->setData(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($form, $ban)
    {
        $ban->setIPs($form->get('ip_addresses')->getData())
            ->setExpiration($this->getExpiration($form))
            ->setReason($form->get('reason')->getData())
            ->setServerMessage($form->get('server_message')->getData())
            ->setAllowServerJoin($form->get('server_join_allowed')->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return \Ban::addBan(
            $form->get('player')->getData()->getId(),
            $this->me->getId(),
            $this->getExpiration($form),
            $form->get('reason')->getData(),
            $form->get('server_message')->getData(),
            $form->get('ip_addresses')->getData(),
            $form->get('server_join_allowed')->getData()
        );
    }

    /**
     * Get the expiration time of the ban based on the fields of the form
     *
     * @param  Form          $form The form
     * @return TimeDate|null
     */
    private function getExpiration($form)
    {
        if ($form->get('automatic_expiration')->getData()) {
            return $form->get('expiration')->getData();
        } else {
            return null;
        }
    }
}
