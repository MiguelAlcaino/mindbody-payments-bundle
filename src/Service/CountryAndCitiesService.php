<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 13/12/17
 * Time: 12:00
 * @github https://github.com/khsing/laravel-world
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;


class CountryAndCitiesService
{
    private $cities = array(
        1 =>
            array(
                'code' => ['AMZ', 'AMA'],
                'name' => 'Amazonas',
            ),
        2 =>
            array(
                'code' => 'ANT',
                'name' => 'Antioquia',
            ),
        3 =>
            array(
                'code' => 'ARA',
                'name' => 'Arauca',
            ),
        4 =>
            array(
                'code' => 'ATL',
                'name' => 'Atlantico',
            ),
        5 =>
            array(
                'code' => ['BDC', 'DC'],
                'name' => 'Bogota D.C.',
            ),
        6 =>
            array(
                'code' => 'BOL',
                'name' => 'Bolivar',
            ),
        7 =>
            array(
                'code' => 'BOY',
                'name' => 'Boyaca',
            ),
        8 =>
            array(
                'code' => 'CAL',
                'name' => 'Caldas',
            ),
        9 =>
            array(
                'code' => 'CAQ',
                'name' => 'Caqueta',
            ),
        10 =>
            array(
                'code' => 'CAS',
                'name' => 'Casanare',
            ),
        11 =>
            array(
                'code' => 'CAU',
                'name' => 'Cauca',
            ),
        12 =>
            array(
                'code' => 'CES',
                'name' => 'Cesar',
            ),
        13 =>
            array(
                'code' => 'CHO',
                'name' => 'Choco',
            ),
        14 =>
            array(
                'code' => 'COR',
                'name' => 'Cordoba',
            ),
        15 =>
            array(
                'code' => ['CAM', 'CUN'],
                'name' => 'Cundinamarca',
            ),
        16 =>
            array(
                'code' => ['GNA', 'GUA'],
                'name' => 'Guainia',
            ),
        17 =>
            array(
                'code' => ['GJR', 'LAG'],
                'name' => 'Guajira',
            ),
        18 =>
            array(
                'code' => ['GVR', 'GUV'],
                'name' => 'Guaviare',
            ),
        19 =>
            array(
                'code' => 'HUI',
                'name' => 'Huila',
            ),
        20 =>
            array(
                'code' => 'MAG',
                'name' => 'Magdalena',
            ),
        21 =>
            array(
                'code' => 'MET',
                'name' => 'Meta',
            ),
        22 =>
            array(
                'code' => 'NAR',
                'name' => 'Narino',
            ),
        23 =>
            array(
                'code' => ['NDS', 'NSA'],
                'name' => 'Norte de Santander',
            ),
        24 =>
            array(
                'code' => 'PUT',
                'name' => 'Putumayo',
            ),
        25 =>
            array(
                'code' => 'QUI',
                'name' => 'Quindio',
            ),
        26 =>
            array(
                'code' => 'RIS',
                'name' => 'Risaralda',
            ),
        27 =>
            array(
                'code' => 'SAP',
                'name' => 'San Andres y Providencia',
            ),
        28 =>
            array(
                'code' => 'SAN',
                'name' => 'Santander',
            ),
        29 =>
            array(
                'code' => 'SUC',
                'name' => 'Sucre',
            ),
        30 =>
            array(
                'code' => 'TOL',
                'name' => 'Tolima',
            ),
        31 =>
            array(
                'code' => ['VDC', 'VAC'],
                'name' => 'Valle del Cauca',
            ),
        32 =>
            array(
                'code' => 'VAU',
                'name' => 'Vaupes',
            ),
        33 =>
            array(
                'code' => ['VIC', 'VID'],
                'name' => 'Vichada',
            ),
    );


    public function getColombianCitiesForForm()
    {
        $choices = [];
        foreach ($this->cities as $city) {
            if (is_array($city['code'])) {
                $choices[$city['name']] = $city['code'][1];
            } else {
                $choices[$city['name']] = $city['code'];
            }

        }

        ksort($choices);
        return $choices;
    }

    public function getCityCodeByName($name)
    {
        foreach ($this->cities as $city) {
            if ($city['name'] === $name) {
                if (is_array($city['code'])) {
                    return $city['code'][1];
                } else {
                    return $city['code'];
                }

            }
        }
        return null;
    }

    public function getCityNameByCode($code)
    {
        foreach ($this->cities as $city) {
            if (is_array($city['code'])) {
                foreach ($city['code'] as $subCode) {
                    if ($subCode === $code) {
                        return $city['name'];
                    }
                }
            } else {
                if ($city['code'] === $code) {
                    return $city['name'];
                }
            }

        }
        return null;
    }
}