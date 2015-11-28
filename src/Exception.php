<?php

    namespace ObjectivePHP\Application;
    
    
    class Exception extends \Exception
    {
        // Workflow related
        const INVALID_EVENT_BINDING = 0x10;

        // Action
        const ACTION_NOT_FOUND = 0x20;

        // Session
        const SESSION_DISABLED = 0x30;

        // Request parameters
        const INVALID_PARAMETER_VALUE = 0x40;
    }