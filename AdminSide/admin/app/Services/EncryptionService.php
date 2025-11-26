<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

/**
 * AES-256-CBC Encryption Service
 * Provides encryption/decryption for sensitive data
 * Uses Laravel's Crypt facade with AES-256-CBC
 * 
 * Compatible with Node.js encryptionService.js
 */
class EncryptionService
{
    /**
     * Encrypt sensitive text data
     * 
     * @param string|null $text
     * @return string|null
     */
    public static function encrypt($text)
    {
        if (empty($text)) {
            return $text;
        }

        try {
            // Laravel's Crypt::encryptString uses AES-256-CBC by default
            return Crypt::encryptString($text);
        } catch (\Exception $e) {
            Log::error('Encryption error: ' . $e->getMessage());
            throw new \Exception('Failed to encrypt data');
        }
    }

    /**
     * Decrypt encrypted text data
     * 
     * @param string|null $encryptedText
     * @return string|null
     */
    public static function decrypt($encryptedText)
    {
        if (empty($encryptedText)) {
            return $encryptedText;
        }

        try {
            return Crypt::decryptString($encryptedText);
        } catch (DecryptException $e) {
            Log::warning('Decryption error: ' . $e->getMessage());
            // Return encrypted data if decryption fails (might not be encrypted)
            return $encryptedText;
        }
    }

    /**
     * Encrypt multiple fields in an array
     * 
     * @param array $data
     * @param array $fields Fields to encrypt
     * @return array
     */
    public static function encryptFields(array $data, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Decrypt multiple fields in an array
     * 
     * @param array $data
     * @param array $fields Fields to decrypt
     * @return array
     */
    public static function decryptFields(array $data, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::decrypt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Decrypt an object/model's fields (for Eloquent models)
     * 
     * @param object $model
     * @param array $fields
     * @return object
     */
    public static function decryptModelFields($model, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($model->$field) && !empty($model->$field)) {
                $model->$field = self::decrypt($model->$field);
            }
        }

        return $model;
    }

    /**
     * Check if user has permission to decrypt data
     * Only police and admin roles can decrypt sensitive data
     * 
     * @param string|null $userRole
     * @return bool
     */
    public static function canDecrypt($userRole)
    {
        $authorizedRoles = ['police', 'admin'];
        return in_array($userRole, $authorizedRoles);
    }
}
