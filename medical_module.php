<?php
if(!isset($required_role)){ http_response_code(403); exit('Access denied'); }
session_start();
require_once __DIR__.'/HealthSystem.php';
if(!isset($_SESSION['ID']) || $_SESSION['ROLE'] != $required_role){ header('Location: ../login.php'); exit; }
$role = $_SESSION['ROLE'];
$obj = new HealthSystem($pdo);
$error = '';
if(empty($_SESSION['supply_token'])){ $_SESSION['supply_token']=bin2hex(random_bytes(32)); }
if($_SERVER['REQUEST_METHOD']=='POST' && $role!='resident'){
    if(!hash_equals($_SESSION['supply_token'], $_POST['token'] ?? '')){ exit('Invalid request'); }
    try{
        if(isset($_POST['add']) && $role=='admin'){
            if(trim($_POST['medicine_name'])=='' || (int)$_POST['quantity']<0 || (int)$_POST['minimum_stock']<0 || (float)$_POST['acquisition_cost']<0 || !in_array($_POST['procurement_type'],['Purchased','Donated']) || !in_array($_POST['category'],['Medicine','Consumable','Equipment'])){ $error='Invalid supply details.'; }
            else{ $obj->add_supply([trim($_POST['medicine_name']),$_POST['category'],$_POST['unit'],$_POST['quantity'],$_POST['minimum_stock'],$_POST['date_procured'],$_POST['procurement_type'],$_POST['source'],$_POST['procurement_type']=='Donated'?0:$_POST['acquisition_cost'],$_POST['expiration_date'] ?: null,$_POST['batch_number'],$_POST['remarks']], $_SESSION['ID']); header('Location: medical_supplies.php'); exit; }
        }
        if(isset($_POST['issue'])){
            $qty=(int)$_POST['quantity'];
            if($qty<=0){ $error='Enter a positive quantity.'; }
            else{ if(!in_array($_POST['transaction_type'] ?? '', ['stock_in','stock_out'], true)) throw new PDOException('Invalid type'); if(!$obj->change_medicine_stock((int)$_POST['medicine_id'], $_POST['transaction_type']=='stock_in'?null:($_POST['resident_id'] ?: null), $_SESSION['ID'], $_POST['transaction_type'], $qty, $_POST['remarks'])) throw new PDOException('Stock update failed'); header('Location: medical_supplies.php'); exit; }
        }
    }catch(PDOException $ex){ $error='Could not save. Check stock, expiration date and entered details.'; }
}
$items = $obj->show_medicines();
if($role == 'resident'){
    $available = [];
    foreach($items as $item){
        $category = trim($item['category'] ?? '');
        $expiration = $item['expiration_date'];
        if(strcasecmp($category, 'Medicine') == 0){
            if(empty($expiration) || $expiration >= date('Y-m-d')){
                $available[] = $item;
            }
        }
    }
    $items = $available;
}
$title = $role == 'resident' ? 'Available Medicines' : 'Medical Supplies';
require __DIR__.'/module_header.php';
?>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if($role=='admin'): ?>
<div class="card p-3 mb-4"><h2 class="h5">Add received supply / batch</h2><form method="post" class="row g-3"><input type="hidden" name="token" value="<?= e($_SESSION['supply_token']) ?>">
<?php foreach(['medicine_name'=>'Supply name','unit'=>'Unit','source'=>'Supplier / donor','batch_number'=>'Batch number','remarks'=>'Remarks'] as $key=>$label): ?><div class="col-md-3"><label><?= e($label) ?></label><input class="form-control" name="<?= e($key) ?>" <?= in_array($key,['medicine_name','unit','source'])?'required':'' ?>></div><?php endforeach; ?>
<div class="col-md-3"><label>Category</label><select name="category" class="form-select"><option>Medicine</option><option>Consumable</option><option>Equipment</option></select></div>
<div class="col-md-3"><label>Procurement type</label><select name="procurement_type" class="form-select"><option>Purchased</option><option>Donated</option></select></div>
<?php foreach(['quantity'=>'Quantity','minimum_stock'=>'Minimum stock','acquisition_cost'=>'Total cost (PHP)'] as $key=>$label): ?><div class="col-md-3"><label><?= e($label) ?></label><input class="form-control" type="number" min="0" step="<?= $key=='acquisition_cost'?'0.01':'1' ?>" name="<?= e($key) ?>" value="0" required></div><?php endforeach; ?>
<div class="col-md-3"><label>Date procured</label><input type="date" name="date_procured" class="form-control" required></div><div class="col-md-3"><label>Expiration date (optional)</label><input type="date" name="expiration_date" class="form-control"></div><div><button name="add" class="btn btn-primary">Save supply</button></div></form></div>
<?php endif; ?>
<div class="card p-3 mb-4 table-responsive"><h2 class="h5">Current inventory</h2><table class="table table-striped"><thead><tr><th>Supply</th><th>Unit</th><th>Stock</th><th>Status</th><?php if($role!='resident'): ?><th>Category</th><th>Procured</th><th>Type</th><th>Source</th><th>Cost</th><th>Expiry</th><th>Batch</th><th>Min.</th><th>Remarks</th><?php endif; ?></tr></thead><tbody>
<?php if(empty($items)): ?>
<tr><td colspan="<?= $role=='resident'?4:13 ?>" class="text-center text-muted"><?= $role=='resident'?'No non-expired medicines are currently listed.':'No supplies recorded.' ?></td></tr>
<?php endif; ?>
<?php foreach($items as $item): $expired=$item['expiration_date'] && $item['expiration_date']<date('Y-m-d'); ?><tr><td><?= e($item['medicine_name']) ?></td><td><?= e($item['unit']) ?></td><td><?= e($item['quantity']) ?></td><td><?= $expired?'Expired':($item['quantity']==0?'Out of stock':($item['quantity']<=$item['minimum_stock']?'Low stock':'Available')) ?></td><?php if($role!='resident'): foreach(['category','date_procured','procurement_type','source','acquisition_cost','expiration_date','batch_number','minimum_stock','remarks'] as $key): ?><td><?= e($item[$key]) ?></td><?php endforeach; endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php if($role!='resident'): ?><div class="card p-3 mb-4"><h2 class="h5">Record stock-in / stock-out</h2><form method="post" class="row g-3"><input type="hidden" name="token" value="<?= e($_SESSION['supply_token']) ?>"><div class="col-md-4"><label>Transaction</label><select class="form-select mb-2" name="transaction_type"><option value="stock_in">Stock-in</option><option value="stock_out">Stock-out</option></select><label>Supply batch</label><select name="medicine_id" class="form-select" required><?php foreach($items as $item): ?><option value="<?= e($item['medicine_id']) ?>"><?= e($item['medicine_name'].' / '.$item['batch_number'].' / stock '.$item['quantity']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label>Quantity</label><input name="quantity" type="number" min="1" class="form-control" required></div><div class="col-md-3"><label>Resident (optional)</label><select name="resident_id" class="form-select"><option value="">General use</option><?php foreach($obj->show_residents() as $r): ?><option value="<?= e($r['resident_id']) ?>"><?= e($r['first_name'].' '.$r['last_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label>Remarks</label><input name="remarks" class="form-control" required></div><div><button name="issue" class="btn btn-primary">Record transaction</button></div></form></div>
<div class="card p-3 table-responsive"><h2 class="h5">Stock transaction history</h2><table class="table"><tr><th>Date</th><th>Supply</th><th>Type</th><th>Quantity</th><th>Recipient</th><th>Remarks</th></tr><?php foreach($obj->show_medicine_transactions() as $t): ?><tr><td><?= e($t['transaction_date']) ?></td><td><?= e($t['medicine_name']) ?></td><td><?= e($t['transaction_type']) ?></td><td><?= e($t['quantity']) ?></td><td><?= e(trim(($t['first_name']??'').' '.($t['last_name']??'')) ?: 'General use') ?></td><td><?= e($t['remarks']) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
<?php require __DIR__.'/module_footer.php'; ?>
