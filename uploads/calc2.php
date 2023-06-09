<?php
function calculate($formula) {
  $formula = str_replace(' ', '', $formula); // удаление пробелов из формулы
  $numbers = array();
  $operators = array();
  $priority = array('^' => 3, '*' => 2, '/' => 2, '+' => 1, '-' => 1);

  $numberBuffer = '';
  for ($i = 0; $i < strlen($formula); $i++) {
    $char = $formula[$i];
    if (ctype_digit($char)) { // если текущий символ - цифра, добавляем его к буферу числа
      $numberBuffer .= $char;
    } else {
      if ($numberBuffer !== '') { // если буфер числа не пустой, добавляем число в массив чисел
        array_push($numbers, (int)$numberBuffer);
        $numberBuffer = '';
      }
      if ($char === '(') { // открывающая скобка
        array_push($operators, '(');
      } else if ($char === ')') { // закрывающая скобка
        while (end($operators) !== '(') {
          $operator = array_pop($operators);
          $b = array_pop($numbers);
          $a = array_pop($numbers);
          array_push($numbers, applyOperator($a, $operator, $b));
        }
        array_pop($operators); // удалить открывающую скобку из стека операторов
      } else if (array_key_exists($char, $priority)) { // оператор
        while (!empty($operators) && end($operators) !== '(' &&
            $priority[$char] <= $priority[end($operators)]) {
          $operator = array_pop($operators);
          $b = array_pop($numbers);
          $a = array_pop($numbers);
          array_push($numbers, applyOperator($a, $operator, $b));
        }
        array_push($operators, $char);
      } else {
        throw new Exception("Invalid character: {$char}");
      }
    }
  }
  if ($numberBuffer !== '') {
    array_push($numbers, (int)$numberBuffer);
  }
  while (!empty($operators)) { // применить оставшиеся операторы
    $operator = array_pop($operators);
    $b = array_pop($numbers);
    $a = array_pop($numbers);
    array_push($numbers, applyOperator($a, $operator, $b));
  }
  if (count($numbers) !== 1 || !is_int($numbers[0])) { // если результат не является целым числом, ошибка
    throw new Exception("Invalid formula");
  }
  return $numbers[0];
}

function applyOperator($a, $operator, $b) {
  switch ($operator) {
    case '+':
      return $a + $b;
    case '-':
      return $a - $b;
    case '*':
      return $a * $b;
    case '/':
      return $a / $b;
    case '^':
      return pow($a, $b);
  }
}

$formula = '1+3*(7/2*(3+5))^3';
echo "Formula: {$formula}\n";
try {
  $result = calculate($formula);
  echo "Result: {$result}\n";
} catch (Exception $e) {
echo "Error: {$e->getMessage()}\n";
}