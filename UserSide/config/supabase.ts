import { createClient, SupabaseClient } from '@supabase/supabase-js';

// Supabase configuration
// Add your Supabase URL and anon key here
// You can find these in your Supabase project settings
const SUPABASE_URL = process.env.EXPO_PUBLIC_SUPABASE_URL || '';
const SUPABASE_ANON_KEY = process.env.EXPO_PUBLIC_SUPABASE_ANON_KEY || '';

// Only create client if credentials are provided
let supabase: SupabaseClient | null = null;

if (SUPABASE_URL && SUPABASE_ANON_KEY && SUPABASE_URL.startsWith('http')) {
  supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    auth: {
      autoRefreshToken: true,
      persistSession: false,
      detectSessionInUrl: false,
    },
  });
  console.log('✅ Supabase client initialized');
} else {
  console.warn('⚠️ Supabase credentials not configured. OTP will use fallback method.');
  console.warn('To enable Supabase OTP, add EXPO_PUBLIC_SUPABASE_URL and EXPO_PUBLIC_SUPABASE_ANON_KEY to your .env file');
}

export { supabase };

/**
 * Supabase SMS OTP Configuration Notes:
 * 
 * To customize the SMS message sent by Supabase:
 * 1. Go to your Supabase Dashboard
 * 2. Navigate to Authentication > Providers > Phone
 * 3. Configure SMS Settings with your SMS provider (Twilio, MessageBird, etc.)
 * 4. Customize the SMS template with:
 *    
 *    Sender ID: AlertDavao
 *    SMS Message Template:
 *    "Your verification code is {{.Token}}. It is valid for 5 minutes. Do not share this code with anyone for your security."
 * 
 * The {{.Token}} placeholder will be replaced with the actual OTP code.
 */
