<?php
/**
 * SnakkaZ Chat - Response Helper
 * Standardized API responses
 */

class Response {
    /**
     * Send JSON response
     */
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Success response
     */
    public static function success($data = [], $message = 'Success') {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }
    
    /**
     * Error response
     */
    public static function error($message = 'An error occurred', $status = 400, $errors = []) {
        self::json([
            'success' => false,
            'error' => $message,
            'errors' => $errors
        ], $status);
    }
    
    /**
     * Validation error
     */
    public static function validationError($errors = []) {
        self::error('Validation failed', 422, $errors);
    }
    
    /**
     * Unauthorized
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }
    
    /**
     * Forbidden
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }
    
    /**
     * Not found
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }
    
    /**
     * Server error
     */
    public static function serverError($message = 'Internal server error') {
        error_log("Server Error: " . $message);
        self::error($message, 500);
    }
}
