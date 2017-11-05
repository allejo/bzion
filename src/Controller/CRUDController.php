<?php

use BZIon\Form\Creator\ModelFormCreator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller with actions for creating, reading, updating and deleting models
 * @package BZiON\Controllers
 */
abstract class CRUDController extends JSONController
{
    /**
     * Make sure that the data of a form is valid, only called when creating a
     * new object
     * @param  Form $form The submitted form
     * @return void
     */
    protected function validateNew($form)
    {
    }

    /**
     * Make sure that the data of a form is valid, only called when editing an
     * existing object
     * @param  Form $form The submitted form
     * @param  PermissionModel $model The model being edited
     * @return void
     */
    protected function validateEdit($form, $model)
    {
    }

    /**
     * Make sure that the data of a form is valid
     * @param  Form $form The submitted form
     * @return void
     */
    protected function validate($form)
    {
    }

    /**
     * Delete a model
     * @param  PermissionModel    $model     The model we want to delete
     * @param  Player             $me        The user who wants to delete the model
     * @param  Closure|null       $onSuccess Something to do when the model is deleted
     * @throws ForbiddenException
     * @return mixed              The response to show to the user
     */
    protected function delete(PermissionModel $model, Player $me, $onSuccess = null)
    {
        if ($model->isDeleted()) {
            // We will have to hard delete the model
            $hard = true;
            $message = 'hardDelete';
            $action = 'Erase forever';
        } else {
            $hard = false;
            $message = 'softDelete';
            $action = 'Delete';
        }

        if (!$this->canDelete($me, $model, $hard)) {
            throw new ForbiddenException($this->getMessage($model, $message, 'forbidden'));
        }

        $successMessage = $this->getMessage($model, $message, 'success');
        $redirection    = $this->redirectToList($model);

        return $this->showConfirmationForm(function () use ($model, $hard, $redirection, $onSuccess) {
            if ($hard) {
                $model->wipe();
            } else {
                $model->delete();
            }

            if ($onSuccess) {
                $response = $onSuccess();
                if ($response instanceof Response) {
                    return $response;
                }
            }

            return $redirection;
        }, $this->getMessage($model, $message, 'confirm'), $successMessage, $action);
    }

    protected function restore(PermissionModel $model, Player $me, $onSuccess)
    {
        if (!$this->canDelete($me, $model)) {
            throw new ForbiddenException($this->getMessage($model, 'restore', 'forbidden'));
        }

        if (!$model->isDeleted()) {
            throw new LogicException('You cannot restore an object that is not marked as deleted.');
        }

        $successMessage = $this->getMessage($model, 'restore', 'success');

        return $this->showConfirmationForm(function () use ($model, $successMessage, $onSuccess) {
            $model->restore();

            if ($onSuccess) {
                $response = $onSuccess();
                if ($response instanceof Response) {
                    return $response;
                }
            }

            return $this->redirectTo($model);
        }, $this->getMessage($model, 'restore', 'confirm'), $successMessage, 'Restore');
    }

    /**
     * Create a model
     *
     * This method requires that you have implemented enter() and a form creator
     * for the model
     *
     * @param  Player             $me The user who wants to create the model
     * @param  Closure|null       $onSuccess The function to call on success
     * @throws ForbiddenException
     * @return mixed              The response to show to the user
     */
    protected function create(Player $me, $onSuccess = null)
    {
        if (!$this->canCreate($me)) {
            throw new ForbiddenException($this->getMessage($this->getName(), 'create', 'forbidden'));
        }

        $creator = $this->getFormCreator();
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isSubmitted()) {
            $this->validate($form);
            $this->validateNew($form);
            if ($form->isValid()) {
                $model = $creator->enter($form);
                $this->getFlashBag()->add("success",
                     $this->getMessage($model, 'create', 'success'));

                if ($onSuccess) {
                    $response = $onSuccess($model);
                    if ($response instanceof Response) {
                        return $response;
                    }
                }

                return $this->redirectTo($model);
            }
        }

        return array("form" => $form->createView());
    }

    /**
     * Edit a model
     *
     * This method requires that you have implemented update() and a form creator
     * for the model
     *
     * @param  PermissionModel    $model The model we want to edit
     * @param  Player             $me    The user who wants to edit the model
     * @param  string             $type  The name of the variable to pass to the view
     * @throws ForbiddenException
     * @return mixed              The response to show to the user
     */
    protected function edit(PermissionModel $model, Player $me, $type)
    {
        if (!$this->canEdit($me, $model)) {
            throw new ForbiddenException($this->getMessage($model, 'edit', 'forbidden'));
        }

        $creator = $this->getFormCreator($model);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isSubmitted()) {
            $this->validate($form);
            $this->validateEdit($form, $model);
            if ($form->isValid()) {
                $creator->update($form, $model);
                $this->getFlashBag()->add("success",
                    $this->getMessage($model, 'edit', 'success'));

                return $this->redirectTo($model);
            }
        }

        return array("form" => $form->createView(), $type => $model);
    }

    /**
     * Find whether a player can delete a model
     *
     * @param  Player          $player The player who wants to delete the model
     * @param  PermissionModel $model  The model that will be deleted
     * @param  bool         $hard   Whether to hard-delete the model instead of soft-deleting it
     * @return bool
     */
    protected function canDelete($player, $model, $hard = false)
    {
        return $player->canDelete($model, $hard);
    }

    /**
     * Find whether a player can create a model
     *
     * @param  Player  $player The player who wants to create a model
     * @return bool
     */
    protected function canCreate($player)
    {
        $modelName = $this->getName();

        return $player->canCreate($modelName);
    }

    /**
     * Find whether a player can edit a model
     *
     * @param  Player          $player The player who wants to delete the model
     * @param  PermissionModel $model  The model which will be edited
     * @return bool
     */
    protected function canEdit($player, $model)
    {
        return $player->canEdit($model);
    }

    /**
     * Get a redirection response to a model
     *
     * Goes to a list of models of the same type if the provided model does not
     * have a URL
     *
     * @param  ModelInterface $model The model to redirect to
     * @return Response
     */
    protected function redirectTo($model)
    {
        if ($model instanceof UrlModel) {
            return new RedirectResponse($model->getUrl());
        } else {
            return $this->redirectToList($model);
        }
    }

    /**
     * Get a redirection response to a list of models
     *
     * @param  ModelInterface $model The model to whose list we should redirect
     * @return Response
     */
    protected function redirectToList($model)
    {
        $route = $model->getRouteName('list');
        $url = Service::getGenerator()->generate($route);

        return new RedirectResponse($url);
    }

    /**
     * Dynamically get the form to show to the user
     *
     * @param  \Model|null      $model The model being edited, `null` if we're creating one
     * @return ModelFormCreator
     */
    private function getFormCreator($model = null)
    {
        $type = ($model instanceof Model) ? $model->getType() : $this->getName();
        $type = ucfirst($type);

        $creatorClass = "\\BZIon\\Form\\Creator\\{$type}FormCreator";
        $creator = new $creatorClass($model, $this->getMe(), $this);

        return $creator;
    }

    /**
     * Get a message to show to the user
     * @todo   Use the $escape parameter
     * @param  \ModelInterface|string $model  The model (or type) to show a message for
     * @param  string                 $action The action that will be performed (softDelete, hardDelete, create or edit)
     * @param  string                 $status The message's status (confirm, error or success)
     * @return string
     */
    private function getMessage($model, $action, $status, $escape = true)
    {
        if ($model instanceof Model) {
            $type = strtolower($model->getTypeForHumans());

            if ($model instanceof NamedModel) {
                // Twig will not escape the message on confirmation forms
                $name = $model->getName();
                if ($status == 'confirm') {
                    $name = Model::escape($name);
                }

                $messages = $this->getMessages($type, $name);

                return $messages[$action][$status]['named'];
            } else {
                $messages = $this->getMessages($type);

                return $messages[$action][$status]['unnamed'];
            }
        } else {
            $messages = $this->getMessages(strtolower($model));

            return $messages[$action][$status];
        }
    }

    /**
     * Get a list of messages to show to the user
     * @param  string $type The type of the model that the message refers to
     * @param  string $name The name of the model
     * @return array
     */
    protected function getMessages($type, $name = '')
    {
        return array(
            'hardDelete' => array(
                'confirm' => array(
                    'named' => <<<"WARNING"
Are you sure you want to wipe <strong>$name</strong>?<br />
<strong><em>DANGER</em></strong>: This action will <strong>permanently</strong>
erase the $type from the database, including any objects directly related to it!
WARNING
                ,
                    'unnamed' => <<<"WARNING"
Are you sure you want to wipe this $type?<br />
<strong><em>DANGER</em></strong>: This action will <strong>permanently</strong>
erase the $type from the database, including any objects directly related to it!
WARNING
                ),
                'forbidden' => array(
                    'named'   => "You are not allowed to delete the $type $name",
                    'unnamed' => "You are not allowed to delete this $type",
                ),
                'success' => array(
                    'named'   => "The $type $name was permanently erased from the database",
                    'unnamed' => "The $type has been permanently erased from the database",
                ),
            ),
            'softDelete' => array(
                'confirm' => array(
                    'named'   => "Are you sure you want to delete <strong>$name</strong>?",
                    'unnamed' => "Are you sure you want to delete this $type?",
                ),
                'forbidden' => array(
                    'named'   => "You are not allowed to delete the $type $name",
                    'unnamed' => "You are not allowed to delete this $type",
                ),
                'success' => array(
                    'named'   => "The $type $name was deleted successfully",
                    'unnamed' => "The $type was deleted successfully",
                ),
            ),
            'restore' => array(
                'confirm' => array(
                    'named'   => "Are you sure you want to restore <strong>$name</strong>?",
                    'unnamed' => "Are you sure you want to restore this $type?",
                ),
                'forbidden' => array(
                    'named'   => "You are not allowed to restore the $type $name",
                    'unnamed' => "You are not allowed to restore this $type",
                ),
                'success' => array(
                    'named'   => "The $type $name has been restored successfully",
                    'unnamed' => "The $type has been restored successfully",
                ),
            ),
            'edit' => array(
                'forbidden' => array(
                    'named'   => "You are not allowed to edit the $type $name",
                    'unnamed' => "You are not allowed to edit this $type",
                ),
                'success' => array(
                    'named'   => "The $type $name has been successfully updated",
                    'unnamed' => "The $type was updated successfully",
                ),
            ),
            'create' => array(
                'forbidden' => "You are not allowed to create a new $type",
                'success'   => array(
                    'named'   => "The $type $name was created successfully",
                    'unnamed' => "The $type was created successfully",
                ),
            ),
        );
    }
}
