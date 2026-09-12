<?php
/**
 * Customer Orders — redirects to the main order history page.
 * (Kept as a separate file so dashboard links never break.)
 */
require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('customer');

redirect('my-orders.php');
