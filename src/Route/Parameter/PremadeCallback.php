<?php

namespace Hizech\Bliss\Route\Parameter;

enum PremadeCallback {

    case Alphabetic;    // only A–Z / a–z
    case Numeric;       // only digits
    case Alphanumeric;  // letters + digits
    case Slug;          // letters, digits, dash, underscore
    case Any; 
    
}
