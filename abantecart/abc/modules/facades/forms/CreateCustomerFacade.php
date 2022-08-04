<?php
namespace abc\modules\facades\forms;

use abc\core\engine\FormFacade;
use abc\models\customer\Address;
use abc\models\customer\Customer;

class CreateCustomerFacade implements FormFacadeInteface
{
    /**
     * @param $controller
     *
     * @return FormFacade
     * @throws \abc\core\lib\AException
     */
    public static function getForm($controller):FormFacade
    {
     return FormFacade::withModels(
         [
             'type'   => 'form',
             'name'   => 'AccountFrm',
             'action' => $controller->html->getSecureURL('account/create'),
             'csrf'   => true,
         ],
         [
             Customer::class => ['firstname', 'lastname'],
             Address::class  => [],
         ],
         [
             'password_confirmation' =>
                 [
                     'group'    => 'group_name_here',
                     'type'     => 'input',
                     'name'     => 'password_confirmation',
                     'value'    => $controller->request->post['password_confirmation'],
                     'required' => true,
                 ],
             'password' =>
                 [
                     'group'    => 'password',
                     'type'     => 'input',
                     'name'     => 'password',
                     'value'    => $controller->request->post['password'],
                     'required' => true,
                 ],
         ],
         [
             'firstname' => [
                 'sort_order' => 30,
                 'group' => 'general',
             ],
             'lastname' => [
                 'sort_order' => 20,
                 'group' => 'general',
             ],
             'password_confirmation' => [
                 'sort_order' => 1,
                 'group' => 'password',
             ]
         ]
     );
    }

}
