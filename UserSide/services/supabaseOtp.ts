import { supabase } from '../config/supabase';
import { getOptimalBackendUrl } from '../config/backend';

export interface OtpResult {
  success: boolean;
  message: string;
  error?: any;
  debugOtp?: string; // For fallback development mode
}

/**
 * Send OTP to phone number via Supabase (or fallback to backend)
 * @param phone - Phone number in international format (e.g., +639123456789)
 * @returns Promise with result
 */
export async function sendSupabaseOtp(phone: string): Promise<OtpResult> {
  // If Supabase is not configured, use fallback backend OTP
  if (!supabase) {
    console.log('⚠️ Supabase not configured, using fallback backend OTP');
    return sendFallbackOtp(phone);
  }

  try {
    console.log('📱 Sending OTP via Supabase to:', phone);
    
    const { data, error } = await supabase.auth.signInWithOtp({
      phone: phone,
      options: {
        channel: 'sms',
      },
    });

    if (error) {
      console.error('❌ Supabase OTP send error:', error);
      return {
        success: false,
        message: error.message || 'Failed to send OTP',
        error,
      };
    }

    console.log('✅ OTP sent successfully via Supabase');
    return {
      success: true,
      message: 'OTP sent successfully',
    };
  } catch (err: any) {
    console.error('❌ Exception sending OTP:', err);
    return {
      success: false,
      message: err.message || 'Failed to send OTP',
      error: err,
    };
  }
}

/**
 * Fallback: Send OTP via backend when Supabase is not configured
 */
async function sendFallbackOtp(phone: string): Promise<OtpResult> {
  try {
    const backendUrl = await getOptimalBackendUrl();
    const response = await fetch(`${backendUrl}/api/send-otp`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ phone, purpose: 'register' })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      return {
        success: false,
        message: data.message || 'Failed to send OTP',
      };
    }
    
    // Log OTP for development
    if (data.debugOtp) {
      console.log('🔐🔐🔐 FALLBACK OTP CODE:', data.debugOtp, '🔐🔐🔐');
    }
    
    return {
      success: true,
      message: 'OTP sent successfully',
      debugOtp: data.debugOtp,
    };
  } catch (err: any) {
    console.error('❌ Fallback OTP send error:', err);
    return {
      success: false,
      message: err.message || 'Failed to send OTP',
      error: err,
    };
  }
}

/**
 * Verify OTP code via Supabase (or fallback to backend)
 * @param phone - Phone number in international format
 * @param token - 6-digit OTP code
 * @returns Promise with result
 */
export async function verifySupabaseOtp(phone: string, token: string): Promise<OtpResult> {
  // If Supabase is not configured, use fallback backend verification
  if (!supabase) {
    console.log('⚠️ Supabase not configured, using fallback backend OTP verification');
    return verifyFallbackOtp(phone, token);
  }

  try {
    console.log('🔐 Verifying OTP via Supabase for:', phone, 'code:', token);
    
    const { data, error } = await supabase.auth.verifyOtp({
      phone: phone,
      token: token,
      type: 'sms',
    });

    if (error) {
      console.error('❌ Supabase OTP verify error:', error);
      return {
        success: false,
        message: error.message || 'Invalid OTP code',
        error,
      };
    }

    console.log('✅ OTP verified successfully via Supabase');
    
    // Sign out immediately since we're only using this for verification
    // We don't want to maintain a Supabase session
    await supabase.auth.signOut();
    
    return {
      success: true,
      message: 'Phone number verified successfully',
    };
  } catch (err: any) {
    console.error('❌ Exception verifying OTP:', err);
    return {
      success: false,
      message: err.message || 'Failed to verify OTP',
      error: err,
    };
  }
}

/**
 * Fallback: Verify OTP via backend when Supabase is not configured
 */
async function verifyFallbackOtp(phone: string, token: string): Promise<OtpResult> {
  try {
    const backendUrl = await getOptimalBackendUrl();
    const response = await fetch(`${backendUrl}/api/verify-otp`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ phone, code: token, purpose: 'register' })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      return {
        success: false,
        message: data.message || 'Invalid OTP code',
      };
    }
    
    return {
      success: true,
      message: 'Phone number verified successfully',
    };
  } catch (err: any) {
    console.error('❌ Fallback OTP verify error:', err);
    return {
      success: false,
      message: err.message || 'Failed to verify OTP',
      error: err,
    };
  }
}

/**
 * Normalize phone number to international format
 * Converts Philippine numbers starting with 0 to +63 format
 * @param phone - Phone number
 * @returns Normalized phone number
 */
export function normalizePhoneNumber(phone: string): string {
  let normalized = phone.trim().replace(/\s+/g, '');
  
  // If starts with 0, replace with +63
  if (normalized.startsWith('0')) {
    normalized = '+63' + normalized.slice(1);
  }
  
  // If doesn't start with +, assume it's Philippine and add +63
  if (!normalized.startsWith('+')) {
    normalized = '+63' + normalized;
  }
  
  return normalized;
}
