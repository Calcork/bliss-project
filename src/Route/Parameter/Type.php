<?php

namespace Hizech\Bliss\Route\Parameter;

enum Type {
    
    // Is required and its value is required too
    case Required;
    // Has value and is either called with value or neither at all
    case Optional;
    // Has no value and is always optional
    case Flag;

}