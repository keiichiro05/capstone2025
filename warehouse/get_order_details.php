<?php
include('../konekdb.php');

$po_id = $_GET['po_id'] ?? null;
if (!$po_id) die('Invalid request');

// Get order header
$order_query = mysqli_query($mysqli, "SELECT po.*, c.customer_name, c.address, c.phone, c.email 
                                    FROM purchase_order po
                                    JOIN customers c ON po.customer_id = c.customer_id
                                    WHERE po.po_id = '$po_id'");
$order = mysqli_fetch_assoc($order_query);

// Get order items
$items_query = mysqli_query($mysqli, "SELECT poi.*, p.product_name 
                                    FROM purchase_order_item poi
                                    JOIN products p ON poi.product_code = p.product_code
                                    WHERE poi.po_id = '$po_id'");
?>

<div class="order-details">
    <h3>Order Information</h3>
    <table class="table table-bordered">
        <tr>
            <th width="20%">PO Number</th>
            <td><?= $order['po_no'] ?></td>
        </tr>
        <tr>
            <th>Customer</th>
            <td><?= $order['customer_name'] ?></td>
        </tr>
        <tr>
            <th>Order Date</th>
            <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
        </tr>
        <tr>
            <th>Delivery Status</th>
            <td>
                <span class="order-status status-<?= $order['delivery_status'] ?>">
                    <?= ucfirst($order['delivery_status']) ?>
                </span>
                <?php if ($order['processed_at']): ?>
                    <br><small>Processed on: <?= date('d M Y H:i', strtotime($order['processed_at'])) ?></small>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Payment Status</th>
            <td>
                <span class="order-status <?= $order['payment_status'] == 'paid' ? 'status-delivered' : 'status-pending' ?>">
                    <?= ucfirst($order['payment_status']) ?>
                </span>
            </td>
        </tr>
    </table>
</div>

<div class="order-items">
    <h3>Order Items</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while ($item = mysqli_fetch_assoc($items_query)): 
                $grand_total += $item['total_price'];
            ?>
                <tr>
                    <td><?= $item['product_code'] ?></td>
                    <td><?= $item['product_name'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= number_format($item['total_price'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="4" align="right"><strong>Grand Total:</strong></td>
                <td><strong><?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

<?php if ($order['delivery_status'] == 'processed'): ?>
<div class="shipping-info">
    <h3>Shipping Information</h3>
    <table class="table table-bordered">
        <tr>
            <th width="20%">SJPP Number</th>
            <td><?= $order['sjpp_number'] ?></td>
        </tr>
        <tr>
            <th>Shipping Vendor</th>
            <td><?= $order['shipping_vendor'] ?></td>
        </tr>
        <tr>
            <th>Tracking Number</th>
            <td><?= $order['tracking_number'] ?></td>
        </tr>
        <tr>
            <th>Shipping Date</th>
            <td><?= date('d M Y', strtotime($order['shipping_date'])) ?></td>
        </tr>
        <tr>
            <th>Shipping Notes</th>
            <td><?= $order['shipping_notes'] ?></td>
        </tr>
    </table>
</div>
<?php endif; ?>