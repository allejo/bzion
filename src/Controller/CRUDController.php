<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A controller with actions for creating, reading, updating and deleting models
 * @package BZiON\Controllers
 */
abstract class CRUDController extends JSONController
{
    /**
     * Delete a model
     * @param PermissionModel $model The model we want to delete
     * @param Player $me The user who wants to delete the model
     * @return mixed The response to show to the user
     */
    protected function delete(PermissionModel $model, Player $me)
    {
        if (!$this->canDelete($me, $model))
            throw new ForbiddenException($this->getMessage($model, 'softDelete', 'error'));

        $session = $this->getRequest()->getSession();
        $successMessage = $this->getMessage($model, 'softDelete', 'success');

        return $this->showConfirmationForm(function () use (&$model, &$session, $successMessage) {
            $model->delete();
            $session->getFlashBag()->add('success', $successMessage);

            return new RedirectResponse($model->getUrl('list'));
        }, $this->getMessage($model, 'softDelete', 'confirm'), "Delete");
    }

    /**
     * Find whether a player can delete a model
     *
     * @return boolean
     */
    protected function canDelete(Player $player, PermissionModel $model)
    {
        return $player->hasPermission($model->getSoftDeletePermission());
    }

    /**
     * Get a message to show to the user
     * @param Model $model The model to show a message for
     * @param string $action The action that will be performed (softDelete, hardDelete, create or edit)
     * @param string $status The message's status (confirm, error or success)
     * @return string
     */
    protected function getMessage(Model $model, $action, $status)
    {
        $type = strtolower($model->getTypeForHumans());

        if ($model instanceof NamedModel) {
            $name = Model::escape($model->getName());
            $messages = $this->getMessages($type, $name);
            return $messages[$action][$status]['named'];
        } else {
            $messages = $this->getMessages($type);
            return $messages[$action][$status]['unnamed'];
        }
    }

    /**
     * Get a list of messages to show to the user
     * @param string $type The type of the model that the message refers to
     * @param string $name The name of the model
     * @return array
     */
    private function getMessages($type, $name='')
    {
        return array(
            'softDelete' => array(
                'confirm' => array(
                    'named'   => "Are you sure you want to delete <strong>$name</strong>?",
                    'unnamed' => "Are you sure you want to delete this $type?",
                ),
                'error' => array(
                    'named'   => "You cannot delete the $type $name",
                    'unnamed' => "You can't delete this $type",
                ),
                'success' => array(
                    'named'   => "The $type $name was deleted successfully",
                    'unnamed' => "The $type was deleted successfully",
                ),
            ),
        );
    }
}
