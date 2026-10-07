<?php

namespace Controllers;

use \Models\FormRepository;
use \Views\Surveys;

class SurveysController extends DatabaseController
{
    public function execute(): void
    {
        $formRepository = new FormRepository($this->db);
        (new Surveys($formRepository->findAllForms()))->show();
    }
}
