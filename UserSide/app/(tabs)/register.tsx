import React, { useState } from "react";
import { View, Text, Button, TextInput, ScrollView, Platform, TouchableOpacity, Alert, Modal, Pressable } from "react-native";
import Checkbox from "expo-checkbox";
import CaptchaObfuscated, { generateCaptchaWord } from '../../components/CaptchaObfuscated';
import styles from "./styles";
import { useRouter } from 'expo-router';
import { PhoneInput, validatePhoneNumber } from '../../components/PhoneInput';
import { BACKEND_URL } from '../../config/backend';
// import CaptchaWebView from '../../components/CaptchaWebView';
import Constants from 'expo-constants';
// OTP package is installed; we'll use a simple TextInput for the code entry here.

const Register = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmpassword, setConfirmPassword] = useState("");
  const [firstname, setFirstname] = useState("");
  const [lastname, setLastname] = useState("");
  const [contact, setContact] = useState("");
  const [captchaAnswer, setCaptchaAnswer] = useState('');
  const [captchaValid, setCaptchaValid] = useState(false);
  const [captchaWord, setCaptchaWord] = useState(generateCaptchaWord(6));
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpCode, setOtpCode] = useState('');
  const [isChecked, setChecked] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [passwordMatchError, setPasswordMatchError] = useState("");
  const [passwordsMatch, setPasswordsMatch] = useState(false);
  const [registrationError, setRegistrationError] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const router = useRouter();

  const handleRegister = async () => {
    setRegistrationError("");
    
    if (!isChecked) {
      alert("You must accept the Terms & Conditions before registering.");
      return;
    }

    // Validate required fields
    if (!firstname || !lastname || !email || !contact || !password || !confirmpassword) {
      Alert.alert('Missing Fields', 'Please fill in all required fields marked with *');
      return;
    }

    // Validate password length
    if (password.length < 6) {
      Alert.alert('Invalid Password', 'Password must be at least 6 characters long');
      return;
    }

    if (password !== confirmpassword) {
      setPasswordMatchError("Passwords do not match");
      return;
    }
    
    if (!passwordsMatch) {
      setPasswordMatchError("Passwords do not match");
      return;
    }

    try {
      // Validate phone number
      if (!validatePhoneNumber(contact)) {
        Alert.alert('Invalid Phone', 'Please enter a valid Philippine mobile number (e.g., +639123456789)');
        return;
      }

      if (!captchaValid) {
        Alert.alert('Captcha Required', 'Please type the word shown in the captcha.');
        return;
      }

      setIsLoading(true);
      // Send OTP for registration
      
      // Normalize phone number to match backend format
      let normalizedPhone = contact.trim().replace(/\s+/g, '');
      if (normalizedPhone.startsWith('0')) {
        normalizedPhone = '+63' + normalizedPhone.slice(1);
      }
      
      const sendResp = await fetch(`${BACKEND_URL}/api/send-otp`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: normalizedPhone, purpose: 'register' }),
      });
      const sendData = await sendResp.json();
      setIsLoading(false);
      
      if (!sendResp.ok) {
        Alert.alert('OTP Error', sendData.message || 'Failed to send OTP');
        return;
      }

      // Show OTP in alert for development
      if (sendData.debugOtp) {
        Alert.alert(
          '🔐 Development OTP',
          `Your OTP code is: ${sendData.debugOtp}\n\n(This will be sent via SMS in production)`,
          [{ text: 'OK' }]
        );
      }

      // Show OTP modal to let user input code
      setShowOtpModal(true);
    } catch (error) {
      console.error('Register flow error:', error);
      Alert.alert('Error', 'Failed to start registration.');
      setIsLoading(false);
    }
  };

  const submitOtpAndRegister = async (code: string) => {
    if (!code || code.length !== 6 || isLoading) {
      return; // Wait for complete code or prevent duplicate submissions
    }

    setIsLoading(true);
    try {
      // Normalize phone number to match backend format
      let normalizedPhone = contact.trim().replace(/\s+/g, '');
      if (normalizedPhone.startsWith('0')) {
        normalizedPhone = '+63' + normalizedPhone.slice(1);
      }
      
      console.log('🔐 Verifying register OTP for phone:', normalizedPhone);
      const verifyResp = await fetch(`${BACKEND_URL}/api/verify-otp`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: normalizedPhone, purpose: 'register', code }),
      });
      const verifyData = await verifyResp.json();
      console.log('📥 OTP verification response:', verifyData);
      
      if (!verifyResp.ok) {
        Alert.alert('OTP Error', verifyData.message || 'Incorrect OTP');
        setOtpCode('');
        setIsLoading(false);
        return;
      }

      console.log('✅ OTP verified, proceeding with registration...');
      // Now call the existing register endpoint (which requires phone verification)
      const regResp = await fetch(`${BACKEND_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ firstname, lastname, email, contact, password }),
      });
      const regData = await regResp.json();
      console.log('📥 Registration response:', regData);
      
      setIsLoading(false);
      
      if (regResp.ok) {
        // Clean up state
        setShowOtpModal(false);
        setOtpCode('');
        
        Alert.alert('Verification Email Sent', 'Please check your email to complete registration', [
          { text: 'OK', onPress: () => {
            console.log('🚀 Navigating to login...');
            router.replace('/login');
          }}
        ]);
      } else {
        const errorMessage = regData.message || 'Failed to register';
        setRegistrationError(errorMessage);
        setOtpCode('');
      }
    } catch (err) {
      console.error('❌ submitOtpAndRegister error:', err);
      Alert.alert('Error', 'Failed to verify OTP or register');
      setOtpCode('');
      setIsLoading(false);
    }
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={{ paddingTop: 20, paddingBottom: 200, alignItems: 'center' }}
      keyboardShouldPersistTaps="handled"
    >
      <View style={{ width: '100%', maxWidth: 440, paddingHorizontal: 20 }}>
      {/* Title */}
      <Text style={styles.textTitle}>
        <Text style={styles.alertWelcome}>Alert</Text>
        <Text style={styles.davao}>Davao</Text>
      </Text>

      <Text style={styles.subheadingCenter}>Welcome to AlertDavao!</Text>
      <Text style={styles.normalTxtCentered}>
        Register and Create an Account
      </Text>

      {registrationError ? (
        <Text style={{ color: '#E63946', fontSize: 12, marginBottom: 15, paddingHorizontal: 10, textAlign: 'center' }}>
          {registrationError}
        </Text>
      ) : null}

      {/* Firstname */}
      <Text style={styles.subheading2}>Firstname <Text style={{color: '#ef4444'}}>*</Text></Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your first name"
        value={firstname}
        onChangeText={(text) => setFirstname(text.replace(/[^a-zA-Z\s]/g, ''))}
        maxLength={50}
      />

      {/* Lastname */}
      <Text style={styles.subheading2}>Lastname <Text style={{color: '#ef4444'}}>*</Text></Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your last name"
        value={lastname}
        onChangeText={(text) => setLastname(text.replace(/[^a-zA-Z\s]/g, ''))}
        maxLength={50}
      />

      {/* Email */}
      <Text style={styles.subheading2}>Email <Text style={{color: '#ef4444'}}>*</Text></Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your email"
        value={email}
        onChangeText={(text) => setEmail(text.trim().toLowerCase())}
        keyboardType="email-address"
        autoCapitalize="none"
        maxLength={100}
      />

      {/* Contact */}
      <Text style={styles.subheading2}>Contact Number <Text style={{color: '#ef4444'}}>*</Text></Text>
      <PhoneInput
        value={contact}
        onChangeText={setContact}
        placeholder="9XX XXX XXXX"
      />

      {/* Password */}
      <Text style={styles.subheading2}>Password <Text style={{color: '#ef4444'}}>*</Text></Text>
      <Text style={{fontSize: 11, color: '#6b7280', marginBottom: 6}}>
        Must be at least 6 characters long
      </Text>
      <View style={{ position: 'relative' }}>
        <TextInput
          style={[styles.input, { paddingRight: 50 }]}
          placeholder="Enter your password"
          value={password}
          onChangeText={(text) => {
            setPassword(text);
            setPasswordMatchError("");
            // Check if passwords match when typing
            if (confirmpassword) {
              if (text === confirmpassword) {
                setPasswordsMatch(true);
              } else {
                setPasswordsMatch(false);
              }
            }
          }}
          secureTextEntry={!showPassword}
          minLength={6}
        />
        <Pressable
          onPress={() => setShowPassword(!showPassword)}
          style={{
            position: 'absolute',
            right: 10,
            top: 13,
            paddingHorizontal: 8,
          }}
        >
          <Text style={{ fontSize: 12, color: '#1D3557', fontWeight: '600' }}>
            {showPassword ? 'HIDE' : 'SHOW'}
          </Text>
        </Pressable>
      </View>

      {/* Confirm Password */}
      <Text style={styles.subheading2}>Confirm Password <Text style={{color: '#ef4444'}}>*</Text></Text>
      <View style={{ position: 'relative' }}>
        <TextInput
          style={[styles.input, { paddingRight: 50 }]}
          placeholder="Re-enter your password"
          value={confirmpassword}
          onChangeText={(text) => {
            setConfirmPassword(text);
            setPasswordMatchError("");
            // Check if passwords match when typing
            if (password) {
              if (text === password) {
                setPasswordsMatch(true);
              } else {
                setPasswordsMatch(false);
              }
            }
          }}
          secureTextEntry={!showConfirmPassword}
        />
        <Pressable
          onPress={() => setShowConfirmPassword(!showConfirmPassword)}
          style={{
            position: 'absolute',
            right: 10,
            top: 13,
            paddingHorizontal: 8,
          }}
        >
          <Text style={{ fontSize: 12, color: '#1D3557', fontWeight: '600' }}>
            {showConfirmPassword ? 'HIDE' : 'SHOW'}
          </Text>
        </Pressable>
      </View>
      {confirmpassword ? (
        <View style={{ marginTop: -10, marginBottom: 15 }}>
          {passwordsMatch ? (
            <Text style={{ color: '#10B981', fontSize: 12, fontWeight: '500' }}>
              ✓ Passwords match
            </Text>
          ) : (
            <Text style={{ color: '#E63946', fontSize: 12 }}>
              ✗ Passwords do not match
            </Text>
          )}
        </View>
      ) : null}

      {/* Checkbox with disclaimer */}
      <View
        style={{
          flexDirection: "row",
          alignItems: "flex-start",
          marginTop: 10,
        }}
      >
        <Checkbox
          value={isChecked}
          onValueChange={setChecked}
          color={isChecked ? "#1D3557" : undefined}
        />
        <Text
          style={{
            fontSize: 12,
            color: "#555",
            marginLeft: 8,
            marginBottom: 15,
            flex: 1,
          }}
        >
          By clicking you agree to accept our{" "}
          <Text style={{ color: "#1D3557", fontWeight: "bold" }}>
            Terms & Conditions
          </Text>
          {", that you are over 18 and aware of our reporting policies!"}
        </Text>
      </View>

      {/* Obfuscated text captcha */}
      <View style={{ marginTop: 12, marginBottom: 12 }}>
        <Text style={{ fontSize: 14, fontWeight: '500', color: '#374151', marginBottom: 8 }}>
          Security Check <Text style={{color: '#ef4444'}}>*</Text>
        </Text>
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 }}>
          <CaptchaObfuscated word={captchaWord} />
          <Pressable
            onPress={() => {
              const newWord = generateCaptchaWord(6);
              setCaptchaWord(newWord);
              setCaptchaAnswer('');
              setCaptchaValid(false);
              console.log('🔄 Captcha refreshed:', newWord);
            }}
            style={{
              backgroundColor: '#3b82f6',
              paddingVertical: 10,
              paddingHorizontal: 12,
              borderRadius: 8,
              height: 44,
              minWidth: 44,
              justifyContent: 'center',
              alignItems: 'center',
            }}
          >
            <Text style={{ color: '#ffffff', fontSize: 20, fontWeight: '600' }}>↻</Text>
          </Pressable>
        </View>
        <TextInput
          style={styles.input}
          placeholder="Enter the code above"
          value={captchaAnswer}
          onChangeText={text => {
            const limited = text.replace(/[^A-Z0-9]/gi, '').slice(0, 6);
            setCaptchaAnswer(limited);
            setCaptchaValid(limited.trim().toUpperCase() === captchaWord);
          }}
          autoCapitalize="characters"
          maxLength={6}
        />
      </View>

      <Modal visible={showOtpModal} animationType="slide" transparent={true}>
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <View style={{ width: 320, padding: 20, backgroundColor: '#fff', borderRadius: 8 }}>
            <Text style={{ fontSize: 16, marginBottom: 4, fontWeight: '600' }}>Enter OTP sent to your phone</Text>
            <Text style={{ fontSize: 12, color: '#666', marginBottom: 12 }}>Auto-proceeds when complete</Text>
            <TextInput
              style={[styles.input, { marginBottom: 12, backgroundColor: '#fff', textAlign: 'center', fontSize: 24, letterSpacing: 8, fontWeight: '600' }]}
              placeholder="000000"
              placeholderTextColor="#ccc"
              value={otpCode}
              onChangeText={async (code) => {
                setOtpCode(code);
                // Auto-submit when 6 digits are entered - but not if already loading
                if (code.length === 6 && !isLoading) {
                  console.log('📝 OTP code complete, triggering submission:', code);
                  await submitOtpAndRegister(code);
                }
              }}
              keyboardType="number-pad"
              maxLength={6}
              autoFocus={true}
              editable={true}
              selectTextOnFocus={true}
            />
          </View>
        </View>
      </Modal>

      <Button
        title="Register"
        onPress={handleRegister}
        disabled={!isChecked || !captchaValid}
        color={isChecked && captchaValid ? "#1D3557" : "#999"}
      />

      {/* Link for users who already have an account */}
      <View style={{ marginTop: 20, alignItems: 'center' }}>
        <Text style={{ color: '#555' }}>I already have an account? </Text>
        <TouchableOpacity onPress={() => router.push('/(tabs)/login')}>
          <Text style={{ color: '#1D3557', fontWeight: 'bold', textDecorationLine: 'underline' }}>
            Login here
          </Text>
        </TouchableOpacity>
      </View>
      </View>
    </ScrollView>
  );
};

export default Register;