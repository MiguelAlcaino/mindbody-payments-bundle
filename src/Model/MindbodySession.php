<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Model;

abstract class MindbodySession
{
    const MINDBODY_CLIENT_ID_VAR_NAME                         = 'mindbody_client_ID';
    const MINDBODY_CLIENT_EMAIL_VAR_NAME                      = 'mindbody_client_email';
    const MINDBODY_CLIENT_GUID_VAR_NAME                       = 'mindbody_client_guid';
    const MINDBODY_SELECTED_SERVICE_ID_VAR_NAME               = 'mindbody_service';
    const MINDBODY_USER_PREFERRED_LOCATION_ID_VAR_NAME        = 'preferredLocationId';
    const MINDBODY_SHOULD_CLIENT_LOCATION_BE_UPDATED_VAR_NAME = 'updateUserLocation';
    const MINDBODY_SELECTED_SERVICE_NAME_VAR_NAME             = 'itemName';
    const MINDBODY_GRAND_TOTAL_VAR_NAME                       = 'grandTotal';
    const MINDBODY_REAL_LOCATIONS_VAR_NAME                    = 'locations';
    const MINDBODY_DISCOUNT_AMOUNT_VAR_NAME                   = 'discountAmount';
    const MINDBODY_DISCOUNT_CODE_USED_VAR_NAME                = 'discountCode';
    const MINDBODY_SELECTED_PAYMENT_METHOD_ID_VAR_NAME        = 'mindbody_payment_method_id';

    const CREDIT_CARD_HOLDER_NAME_VAR_NAME      = 'added_credit_card_holder_name';
    const CREDIT_CARD_LAST_FOUR_DIGITS_VAR_NAME = 'added_credit_card_last_four_digits';
}