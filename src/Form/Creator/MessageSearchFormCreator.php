<?php
/**
 * This file contains a form creator to search Message(s)
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;


/**
 * Form creator for searching messages
 */
class MessageSearchFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('q', 'search')
            ->setAction(\Service::getGenerator()->generate('message_search'))
            ->setMethod('GET');
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormOptions()
    {
        return array(
            'csrf_protection' => false
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getName()
    {
        return '';
    }
}
