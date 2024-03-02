<?php

include("../db.php");
include("../auth.php");

$acc_id = $_REQUEST['id'];
$overtime_hours = $_POST['overtime'];
$bonus = $_POST['bonus'];
$deduction_sum = 0;



$work_day_query = mysqli_query($c, "SELECT * FROM account_info JOIN employee ON account_info.employee_id=employee.emp_id WHERE acc_info_id='$acc_id'");
$work_day_row = mysqli_fetch_assoc($work_day_query);
$days_full_day = $work_day_row['days_full_day'];
$days_half_day = $work_day_row['days_half_day'];
$selected_employee = $work_day_row['emp_id'];

$overtime_query = mysqli_query($c, "SELECT * FROM overtime WHERE ot_id='1'");
$overtime_row = mysqli_fetch_assoc($overtime_query);
$overtime = $overtime_hours * $overtime_row['rate'];

$salary_query = mysqli_query($c, "SELECT * FROM department JOIN employee ON department.dept_id=employee.dept WHERE emp_id='$selected_employee'");
$salary_row = mysqli_fetch_assoc($salary_query);
$salary_rate = $salary_row['dept_salary_rate'];

$total_gross_pay = ($salary_rate * $days_full_day) + (($salary_rate / 2) * $days_half_day) + $bonus + $overtime;

$sql = mysqli_query($c, "UPDATE account_info SET total_gross_pay = '$total_gross_pay' WHERE acc_info_id = '$acc_id'");



// 
$query1 = mysqli_query($c, "SELECT * from deductions WHERE deduction_id = 1");
while ($row = mysqli_fetch_array($query1)) {
  $id = $row['deduction_id'];
  $philhealth_p = $row['deduction_percent'];
}

$query3 = mysqli_query($c, "SELECT * from deductions WHERE deduction_id = 3");
while ($row = mysqli_fetch_array($query3)) {
  $id = $row['deduction_id'];
  $GSIS_p = $row['deduction_percent'];
}

$query4 = mysqli_query($c, "SELECT * from deductions WHERE deduction_id = 4");
while ($row = mysqli_fetch_array($query4)) {
  $id = $row['deduction_id'];
  $PAGIBIG_p = $row['deduction_percent'];
}

$query5 = mysqli_query($c, "SELECT * from deductions WHERE deduction_id = 5");
while ($row = mysqli_fetch_array($query5)) {
  $id = $row['deduction_id'];
  $SSS_p = $row['deduction_percent'];
}




$tax = 0;
if ($total_gross_pay >= 666667) {
  $tax = (($total_gross_pay - 666667) * 0.35) + 183541.80;
} else if ($total_gross_pay >= 166667) {
  $tax = (($total_gross_pay - 166667) * 0.30) + 33541.80;
} else if ($total_gross_pay >= 66667) {
  $tax = (($total_gross_pay - 66667) * 0.25) + 8541.80;
} else if ($total_gross_pay >= 33333) {
  $tax = (($total_gross_pay - 33333) * 0.20) + 1875;
} else if ($total_gross_pay >= 20833) {
  $tax = (($total_gross_pay - 20833) * 0.15);
}

// 


$check_query = mysqli_query($c, "SELECT * FROM account_info WHERE acc_info_id='$acc_id'");
$check_row = mysqli_fetch_assoc($check_query);
$philhealth_check = $check_row['philhealth_check'];
$gsis_check = $check_row['gsis_check'];
$pagibig_check = $check_row['pagibig_check'];
$sss_check = $check_row['sss_check'];

$philhealth = 0;
$GSIS = 0;
$PAGIBIG = 0;
$SSS = 0;
$benefits_deduction = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selected_deductions = $_POST['deduction_selected'];
  if (in_array($philhealth_p, $selected_deductions)) {
    $philhealth = ($total_gross_pay * ($philhealth_p / 100)) / 2;
    $benefits_deduction += $philhealth;
    $philhealth_check = "1";
  } else {
    $philhealth_check = "0";
  }


  if (in_array($GSIS_p, $selected_deductions)) {
    $GSIS = ($total_gross_pay * ($GSIS_p / 100)) / 2;
    $benefits_deduction += $GSIS;
    $gsis_check = "1";
  } else {
    $gsis_check = "0";
  }

  if (in_array($PAGIBIG_p, $selected_deductions)) {
    $PAGIBIG = ($total_gross_pay * ($PAGIBIG_p / 100)) / 2;
    $benefits_deduction += $PAGIBIG;
    $pagibig_check = "1";
  } else {
    $pagibig_check = "0";
  }

  if (in_array($SSS_p, $selected_deductions)) {
    $SSS = ($total_gross_pay * ($SSS_p / 100)) / 2;
    $benefits_deduction += $SSS;
    $sss_check = "1";
  } else {
    $sss_check = "0";
  }
}

$total_deduction = $benefits_deduction + $tax;
$total_net_pay = $total_gross_pay - $total_deduction;

$sql = mysqli_query($c, "UPDATE account_info SET philhealth_check = $philhealth_check, gsis_check = $gsis_check, pagibig_check = $pagibig_check, sss_check = $sss_check WHERE acc_info_id='$acc_id'");
$sql = mysqli_query($c, "UPDATE account_info SET total_deductions = $total_deduction, benefits_deductions = $benefits_deduction, tax_deductions = $tax, total_net_pay = $total_net_pay WHERE acc_info_id='$acc_id'");

if ($sql) {
  ?>
  <script>
    alert('Employee Income successfully updated.');
    window.location.href = '../home/home_income.php';
  </script>
  <?php
} else {
  echo "Invalid";
}

?>