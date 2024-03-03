<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;

class QuizQuestionController extends ResourceController
{
    protected $modelName = 'App\Models\QuizQuestionsModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    // ...
}
