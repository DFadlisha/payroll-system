<?php
// Simple health check endpoint for Zeabur/Render
http_response_code(200);
echo json_encode(["status" => "ok", "timestamp" => time()]);
