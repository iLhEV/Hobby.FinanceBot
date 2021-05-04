<?php

namespace Models;

class WeekSpendings
{
    //Сумма расходов за данную неделю
    private $sum = 0;
    //Номер недели в году
    private $number = 0;
    //Номер месяца, которому принадлежит неделя
    private $month = 0;
    //Номер года, которому принадлежит неделя
    private $year = null;

    public function getSum()
    {
        return $this->sum;
    }

    public function increaseSum($val)
    {
        $this->sum += $val;
    }

    public function setSum($val)
    {
        $this->sum = $val;
    }

    public function setMonth($num)
    {
        $this->month = $num;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }

    //Устанавливает номер недели в году, к которому принадлежит неделя
    public function setNumber($num)
    {
        $this->number = $num;
    }

    public function getNumber()
    {
        return $this->number;
    }
}