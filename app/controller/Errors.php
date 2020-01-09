<?php


class Errors extends MasterController
{
    public function error404()
    {
        $this->view("error/404");
    }
}