<?php

namespace abc\modules\facades\forms;

use abc\core\engine\FormFacade;

interface FormFacadeInteface
{
    public static function getForm($controller):FormFacade;
}
