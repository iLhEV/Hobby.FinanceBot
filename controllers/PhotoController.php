<?php

namespace Controllers;

use Facades\Expense;
use Facades\Tlgr;
use Classes\DateCalc;
use Classes\MoneyFormat;
use Reports\ExpensesReport;

class PhotoController
{
    public function get($rule)
    {
        p('я фото контроллер');
        return true;
    }
}