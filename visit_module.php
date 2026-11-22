<?php
session_start();require_once __DIR__.'/HealthSystem.php';
if(!isset($_SESSION['ID']) || !in_array($required_role??'', ['bhw','nurse'],true) || ($_SESSION['ROLE']??'')!==$required_role){http_response_code(403);exit;}
$role=$required_role;$title='Scheduled Health Visits';$obj=new HealthSystem($pdo);$error='';
$_SESSION['visit_token'] ??= bin2hex(random_bytes(32));
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['visit_token'],$_POST['token']??'')) exit('Invalid request');
 $status=$_POST['status']??'scheduled';
 if(!in_array($status,['scheduled','completed','cancelled'],true)) $error='Invalid status.';
 elseif(isset($_POST['update'])){$q=$pdo->prepare('UPDATE health_visits SET status=? WHERE visit_id=?');$q->execute([$status,(int)$_POST['visit_id']]);}
 else { $date=$_POST['scheduled_date']??'';$type=trim($_POST['visit_type']??'');$d=DateTime::createFromFormat('!Y-m-d',$date);if(!$d || $d->format('Y-m-d')!==$date || $type==='' || empty($_POST['resident_id'])) $error='Enter valid visit details.'; else $obj->add_health_visit((int)$_POST['resident_id'],$type,$date,'scheduled',trim($_POST['notes']??'')); }
 if(!$error){header('Location: health_visits.php');exit;}
}
$visits=$obj->show_health_visits();$residents=$obj->show_residents();require __DIR__.'/module_header.php';?>
<?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?>
<div class="card p-3 mb-4"><h3 class="h5">Schedule a health visit</h3><form method="post" class="row g-3"><input type="hidden" name="token" value="<?=e($_SESSION['visit_token'])?>"><div class="col-md-4"><label>Resident</label><select name="resident_id" class="form-select" required><option value="">Select resident</option><?php foreach($residents as $r): ?><option value="<?=e($r['resident_id'])?>"><?=e($r['first_name'].' '.$r['last_name'])?></option><?php endforeach;?></select></div><div class="col-md-4"><label>Visit type</label><select name="visit_type" class="form-select"><option>Prenatal</option><option>Child health</option><option>Vaccination</option><option>Follow-up</option></select></div><div class="col-md-4"><label>Scheduled date</label><input type="date" name="scheduled_date" class="form-control" required></div><div class="col-12"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div><div><button class="btn btn-primary">Schedule visit</button></div></form></div>
<div class="card p-3 table-responsive"><table class="table table-striped"><thead><tr><th>Resident</th><th>Visit</th><th>Date</th><th>Notes</th><th>Status</th></tr></thead><tbody><?php foreach($visits as $v):?><tr><td><?=e($v['first_name'].' '.$v['last_name'])?></td><td><?=e($v['visit_type'])?></td><td><?=e($v['scheduled_date'])?></td><td><?=e($v['notes'])?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="token" value="<?=e($_SESSION['visit_token'])?>"><input type="hidden" name="visit_id" value="<?=e($v['visit_id'])?>"><select name="status" class="form-select"><?php foreach(['scheduled','completed','cancelled'] as $s):?><option <?= $v['status']===$s?'selected':'' ?>><?=e($s)?></option><?php endforeach;?></select><button name="update" class="btn btn-primary">Save</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/module_footer.php'; ?>
