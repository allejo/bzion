<?php

class MaintenanceController extends HTMLController
{
    public function showAction()
    {
        return array(
            'text' => $this->container->getParameter('bzion.miscellaneous.maintenance')
        );
    }
}
