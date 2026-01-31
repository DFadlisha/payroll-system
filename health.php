<?php
/**
 * Health Check Endpoint
 * Used by Render/Railway/AWS to verify the app is running.
 */
http_response_code(200);
echo "OK";
