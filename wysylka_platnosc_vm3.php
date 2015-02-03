<?php
/**
 * @copyright Copyright (c) 2014 DodatkiJoomla.pl
 * @license GNU/GPL v2
 */
defined('_JEXEC') or die;

class plgSystemWysylka_platnosc_vm2 extends JPlugin
{
    public function onAfterRender()
    {

        if (isset($_REQUEST['option']) && $_REQUEST['option'] == 'com_virtuemart' && isset($_REQUEST['view']) && $_REQUEST['view'] == 'cart' ) {
            // koszyk
            $koszyk = VirtueMartCart::getCart();
            $id_wys = $koszyk->virtuemart_shipmentmethod_id;

            if (!$id_wys) {
                // redirect
                $jap = JFactory::getApplication();
                $jap->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), 'Wybierz najpierw metodę wysyłki.');
                exit();
            } else {
                if ($this->czyPuste() != false) {
                    $params = JComponentHelper::getParams('com_blankcomponent');
                    //var_dump($params);
                    // pola pluginu
                    for ($i = 1; $i <= 50; ++$i) {

                        if ($params->get("shipment_name" . $i) == $id_wys) {

                            $id_platnosci = $params->get('payment_name' . $i);
                            if (!is_array($id_platnosci)) {
                                $id_platnosci = explode("|", $id_platnosci);
                            }
                            break;
                        }
                    }

                    //var_dump($id_platnosci);


                    $body = JResponse::getBody();
                    $regex = '/<input type="radio" name="virtuemart_paymentmethod_id" .+>\s+.+\s+.+br \/>/';
                    preg_match_all($regex, $body, $metody);


                    foreach ($metody[0] as $metoda) {
                        $regex = '/payment_id_[0-9]+/';
                        preg_match($regex, $metoda, $wynik);
                        if (!empty($id_platnosci)) {
                            $wynik = str_replace("payment_id_", "", $wynik);
                            if (!in_array($wynik[0], $id_platnosci)) {
                                $body = str_replace($metoda, "", $body);
                            }
                        }
                    }


                    JResponse::setBody($body);
                } else {
                }
            }
        }
    }

    public function onAfterRoute()
    {
        // czyść id metody płatności, jeżeli ustawioną nową/zmianiona metodę wysyłki - bug!
        if (isset($_REQUEST['option']) && $_REQUEST['option'] == 'com_virtuemart' && isset($_REQUEST['view']) && $_REQUEST['view'] == 'cart' && isset($_REQUEST['task']) && $_REQUEST['task'] == 'setshipment') {
            // koszyk
            $cart = unserialize($_SESSION["__vm"]["vmcart"]);
            $cart->virtuemart_paymentmethod_id = 0;
            $_SESSION["__vm"]["vmcart"] = serialize($cart);
        }
    }

    function czyPuste()
    {

        $params = JComponentHelper::getParams('com_blankcomponent');
        $tab = array();
        for ($i = 1; $i <= 50; ++$i) {
            if ($params->get('shipment_name' . $i)) {
                $tab[] = $params->get('shipment_name' . $i);
            }
        }
        if (count($tab) == 0) {
            return false;
        } else {
            return $tab;
        }
    }
}
