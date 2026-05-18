<?php

namespace App\Enums;

enum PaginationCount: int
{
    case ONE = 1;
    case FIVE = 5;
    case TEN = 10;
    case TWENTY = 20;
    case FIFTY = 50;
    case HUNDRED = 100;
}
