<?php
session_start();
require_once __DIR__.'/HealthSystem.php';
if(!isset($_SESSION['ID']) || !in_array($required_role ?? '', ['admin','bhw'],true) || ($_SESSION['ROLE'] ?? '')!==$required_role){http_response_code(403);exit;}
$role=$required_role; $title='Household Registration';
$_SESSION['household_token'] ??= bin2hex(random_bytes(32));
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['household_token'],$_POST['token']??'')) exit('Invalid request');
 $no=trim($_POST['household_no']??''); $head=trim($_POST['household_head']??'');
 if($no==='' || strlen($no)>30 || $head==='') $error='Enter household number and head.';
 else {
 $q=$pdo->prepare('INSERT INTO households (household_no,household_head,address,contact) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE household_head=VALUES(household_head),address=VALUES(address),contact=VALUES(contact)');
 $q->execute([$no,$head,trim($_POST['address']??''),trim($_POST['contact']??'')]);
 if(!empty($_POST['resident_id'])){ $q=$pdo->prepare('UPDATE residents SET household_no=? WHERE resident_id=?');$q->execute([$no,(int)$_POST['resident_id']]); }
 header('Location: households.php');exit;
 }
}
$pdo->exec("INSERT IGNORE INTO households (household_no) SELECT DISTINCT TRIM(household_no) FROM residents WHERE household_no IS NOT NULL AND TRIM(household_no)<>''");
$rows=$pdo->query('SELECT h.*, (SELECT COUNT(*) FROM residents r WHERE TRIM(r.household_no)=h.household_no) AS members FROM households h ORDER BY h.household_no')->fetchAll();
$obj=new HealthSystem($pdo);$residents=$obj->show_residents();
require __DIR__.'/module_header.php';
?>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="card p-3 mb-4"><h3 class="h5">Register or update household</h3><form method="post" class="row g-3"><input type="hidden" name="token" value="<?= e($_SESSION['household_token']) ?>">
<?php foreach(['household_no'=>'Household number','household_head'=>'Household head','address'=>'Address','contact'=>'Contact number'] as $key=>$label): ?><div class="col-md-6"><label><?= e($label) ?></label><input class="form-control" name="<?= e($key) ?>" <?= in_array($key,['household_no','household_head'])?'required':'' ?>></div><?php endforeach; ?>
<div class="col-md-6"><label>Assign resident (optional)</label><select name="resident_id" class="form-select"><option value="">None</option><?php foreach($residents as $r): ?><option value="<?= e($r['resident_id']) ?>"><?= e($r['first_name'].' '.$r['last_name']) ?></option><?php endforeach; ?></select></div><div><button class="btn btn-primary">Save household</button></div></form></div>
<div class="card p-3 table-responsive"><h3 class="h5">Registered households</h3><table class="table table-striped"><thead><tr><th>Number</th><th>Head</th><th>Address</th><th>Contact</th><th>Members</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><?php foreach(['household_no','household_head','address','contact','members'] as $key): ?><td><?= e($r[$key]) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__.'/module_footer.php'; ?>
