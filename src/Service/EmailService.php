<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 11/03/18
 * Time: 12:03
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;


class EmailService
{
    public function decodeEmailBody($originalEmailBody, $options)
    {
        $user = $options['user'];
        $htmlListOfProducts = '<ul>';
        foreach ($options['products'] as $product) {
            $htmlListOfProducts .= '<li>' . $product->getName() . '</li>';
        }

        $htmlListOfProducts .= '</ul>';
        $originalEmailBody = str_replace('#listOfServices#', $htmlListOfProducts, $originalEmailBody);
        $originalEmailBody = str_replace('#firstName#', $user->getFirstName(), $originalEmailBody);
        $originalEmailBody = str_replace('#lastName#', $user->getLastName(), $originalEmailBody);
        $originalEmailBody = str_replace('#discountPercentage#', $options['discountPercentage'], $originalEmailBody);
        $originalEmailBody = str_replace('#days#', $options['days'], $originalEmailBody);
        $originalEmailBody = str_replace('#beforeAfter#', $options['beforeAfter'] == 'before' ? 'antes' : 'despu&eacute;s', $originalEmailBody);
        $originalEmailBody = str_replace('#discountLink#', '<a target="_blank" href="' . $options['discountLink'] . '">' . $options['discountLink'] . '</a>', $originalEmailBody);
        $originalEmailBody = str_replace('#discountUntil#', $options['discountUntil']->format('d-m-Y H:i'), $originalEmailBody);

        return $originalEmailBody;
    }

    public function randomKey($length = 10)
    {
        $pool = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }
}