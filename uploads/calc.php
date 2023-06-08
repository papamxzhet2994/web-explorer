<?php
$formula = '1+3*(7/2*(3+5))^3'; // вводим формулу в виде текста

// разбиваем формулу на отдельные операнды и операторы
$tokens = preg_split('/([+\-*\/^])/', $formula, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

// устанавливаем приоритет операторов
$operators = ['^', '*', '/', '+', '-'];

// создаем массивы для чисел и операторов
$numbers = [];
$ops = [];

// проходим по всем токенам
foreach ($tokens as $token) {
    if (is_numeric($token)) {
        // если токен является числом, добавляем его в массив чисел
        $numbers[] = (int)$token;
    } else if (in_array($token, $operators)) {
        // если токен является оператором, добавляем его в массив операторов
        while (!empty($ops) && in_array($ops[count($ops) - 1], $operators) && array_search($ops[count($ops) - 1], $operators) >= array_search($token, $operators)) {
            // выполняем операции с более высоким приоритетом, пока это необходимо
            $op = array_pop($ops);
            $num2 = array_pop($numbers);
            $num1 = array_pop($numbers);
            $result = 0;
            switch ($op) {
                case '^':
                    $result = pow($num1, $num2);
                    break;
                case '*':
                    $result = $num1 * $num2;
                    break;
                case '/':
                    $result = $num1 / $num2;
                    break;
                case '+':
                    $result = $num1 + $num2;
                    break;
                case '-':
                    $result = $num1 - $num2;
                    break;
            }
            // добавляем результат обратно в массив чисел
            $numbers[] = $result;
        }
        // добавляем текущий оператор в массив операторов
        $ops[] = $token;
    }
}

// выполняем оставшиеся операции
while (!empty($ops)) {
    $op = array_pop($ops);
    $num2 = array_pop($numbers);
    $num1 = array_pop($numbers);
    $result = 0;
    switch ($op) {
        case '^':
            $result = pow($num1, $num2);
            break;
        case '*':
            $result = $num1 * $num2;
            break;
        case '/':
            $result = $num1 / $num2;
            break;
        case '+':
            $result = $num1 + $num2;
            break;
        case '-':
            $result = $num1 - $num2;
            break;
    }
    // добавляем результат обратно в массив чисел
    $numbers[] = $result;
}

echo 'Результат: ' . $numbers[0];