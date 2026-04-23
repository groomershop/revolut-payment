<?php

namespace Revolut\Payment\Gateway\Validator;

class ResponseValidator extends GeneralResponseValidator
{
    
    /**
     * Get ResponseValidators
     *
     * @return array
     */
    protected function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                
            ]
        );
    }
}
