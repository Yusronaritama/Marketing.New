<?php
$columns = Schema::getColumnListing('biodatas');
echo json_encode($columns);
