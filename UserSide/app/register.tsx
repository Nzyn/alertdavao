import React, { useState, useEffect } from "react";
import { View, Text, Button, TextInput, ScrollView, TouchableOpacity, Alert, Modal, Pressable } from "react-native";
import Checkbox from "expo-checkbox";
import CaptchaObfuscated, { generateCaptchaWord } from '../components/CaptchaObfuscated';
import styles from "./(tabs)/styles";
import { useRouter } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getOptimalBackendUrl } from '../config/backend';
import { useLoading } from '../contexts/LoadingContext';
import { PhoneInput, validatePhoneNumber } from '../components/PhoneInput';

const Register = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmpassword, setConfirmPassword] = useState("");
  const [firstname, setFirstname] = useState("");
  const [lastname, setLastname] = useState("");
  const [contact, setContact] = useState("");
  const [captchaAnswer, setCaptchaAnswer] = useState('');
  const [captchaValid, setCaptchaValid] = useState(false);
  const [captchaWord, setCaptchaWord] = useState(() => {
    const word = generateCaptchaWord(6);
    console.log('🔐 Register captcha word generated:', word);
    return word;
  });
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpCode, setOtpCode] = useState('');
  const [isChecked, setChecked] = useState(false);
  const { showLoading, hideLoading } = useLoading();
  const router = useRouter();

  // Check if user is already logged in
  useEffect(() => {
    const checkLoggedIn = async () => {
      try {
        const userData = await AsyncStorage.getItem('userData');
        if (userData) {
          // User is already logged in, redirect to tabs
          router.replace("/(tabs)");
        }
      } catch (error) {
        console.log("Error checking login status:", error);
      }
    };

    checkLoggedIn();
  }, []);

  const handleRegister = async () => {
    if (!isChecked) {
      alert("You must accept the Terms & Conditions before registering.");
      return;
    }

    // Validate email format
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!email || !emailRegex.test(email)) {
      Alert.alert(
        "Invalid Email",
        "Please enter a valid email address (e.g., example@gmail.com, user@yahoo.com). Email must contain @ and a domain."
      );
      return;
    }

    // Additional check to ensure email has @ symbol
    if (!email.includes('@')) {
      Alert.alert(
        "Invalid Email Format",
        "Email must contain @ symbol. For example: nicolequim@gmail.com"
      );
      return;
    }

    if (password !== confirmpassword) {
      alert("Passwords do not match!");
      return;
    }

    // Validate password requirements
    const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
      Alert.alert(
        "Weak Password",
        "Password must contain:\n• Minimum 8 characters\n• At least one letter\n• At least one number\n• At least one symbol (@$!%*?&)"
      );
      return;
    }

    // Validate phone number
    if (!validatePhoneNumber(contact)) {
      Alert.alert('Invalid Phone', 'Please enter a valid Philippine mobile number (e.g., +639123456789)');
      return;
    }

    if (!captchaValid) {
      Alert.alert('Captcha Required', 'Please type the word shown in the captcha.');
      return;
    }

    showLoading('Sending OTP...');
    try {
      const backendUrl = await getOptimalBackendUrl();
      const apiBase = backendUrl.replace(/\/register$/, '');
      const sendResp = await fetch(`${apiBase}/api/send-otp`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: contact, purpose: 'register' })
      });
      const sendData = await sendResp.json();
      if (!sendResp.ok) {
        Alert.alert('OTP Error', sendData.message || 'Failed to send OTP');
        hideLoading();
        return;
      }

      // Show OTP in alert for development (when SMS provider not configured)
      if (sendData.debugOtp) {
        Alert.alert(
          '🔐 Development OTP',
          `Your OTP code is: ${sendData.debugOtp}\n\n(This will be sent via SMS in production)`,
          [{ text: 'OK' }]
        );
      }

      setShowOtpModal(true);
    } catch (err) {
      console.error('Register OTP error:', err);
      Alert.alert('Error', 'Failed to start registration');
    } finally {
      hideLoading();
    }
  };

  const submitOtpAndRegister = async (code: string) => {
    if (!code || code.length !== 6) {
      return; // Wait for complete code
    }

    showLoading('Verifying OTP...');
    console.log('🔐 Verifying register OTP for phone:', contact);
    try {
      const backendUrl = await getOptimalBackendUrl();
      const apiBase = backendUrl.replace(/\/register$/, '');
      const verifyResp = await fetch(`${apiBase}/api/verify-otp`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: contact, purpose: 'register', code })
      });
      const verifyData = await verifyResp.json();
      console.log('📥 OTP verification response:', verifyData);
      
      if (!verifyResp.ok) {
        Alert.alert('OTP Error', verifyData.message || 'Incorrect OTP');
        setOtpCode('');
        hideLoading();
        return;
      }

      console.log('✅ OTP verified, proceeding with registration...');
      hideLoading();
      showLoading('Creating account...');
      
      // Now call original register handler which requires phone verification
      const response = await fetch(`${await getOptimalBackendUrl()}/register`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ firstname, lastname, email, contact, password })
      });
      const data = await response.json();
      console.log('📥 Registration response:', data);
      
      hideLoading();
      
      if (response.ok) {
        // Clean up state
        setShowOtpModal(false);
        setOtpCode('');
        
        Alert.alert(
          'Verification Email Sent',
          'Please check your email and click the link to complete registration.',
          [{ 
            text: 'OK', 
            onPress: () => {
              console.log('🚀 Navigating to login...');
              router.replace('/(tabs)/login');
            }
          }]
        );
      } else {
        Alert.alert('Registration failed', data.message || 'Failed to register');
        setOtpCode('');
      }
    } catch (err) {
      console.error('❌ submitOtpAndRegister error:', err);
      Alert.alert('Error', 'Failed to verify OTP or register. Please try again.');
      setOtpCode('');
      hideLoading();
    }
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={{ paddingTop: 20, paddingBottom: 200, alignItems: 'center' }}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={true}
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

      {/* Firstname */}
      <Text style={styles.subheading2}>Firstname</Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your first name"
        value={firstname}
        onChangeText={setFirstname}
      />

      {/* Lastname */}
      <Text style={styles.subheading2}>Lastname</Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your last name"
        value={lastname}
        onChangeText={setLastname}
      />

      {/* Email */}
      <Text style={styles.subheading2}>Email</Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your email"
        value={email}
        onChangeText={setEmail}
      />

      {/* Contact */}
      <Text style={styles.subheading2}>Contact Number</Text>
      <PhoneInput
        value={contact}
        onChangeText={setContact}
        placeholder="9XX XXX XXXX"
      />

      {/* Password */}
      <Text style={styles.subheading2}>Password</Text>
      <TextInput
        style={styles.input}
        placeholder="Enter your password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      <Text style={{ fontSize: 11, color: '#666', marginTop: -8, marginBottom: 12 }}>
        Min. 8 characters with letter, number & symbol
      </Text>

      {/* Confirm Password */}
      <Text style={styles.subheading2}>Confirm Password</Text>
      <TextInput
        style={styles.input}
        placeholder="Re-enter your password"
        value={confirmpassword}
        onChangeText={setConfirmPassword}
        secureTextEntry
      />

      {/* Checkbox with disclaimer */}
      <View style={styles.checkboxContainer}>
        <Checkbox
          value={isChecked}
          onValueChange={setChecked}
          color={isChecked ? "#1D3557" : undefined}
        />
        <Text style={styles.checkboxText}>
          By clicking you agree to accept our{" "}
          <Text style={styles.termsText}>
            Terms & Conditions
          </Text>
          ,{"\n"}that you are over 18 and aware of our reporting policies!
        </Text>
      </View>

      {/* Captcha */}
      <View style={{ marginTop: 12, marginBottom: 12 }}>
        <Text style={{ fontSize: 14, fontWeight: '500', color: '#374151', marginBottom: 8 }}>Security Check</Text>
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
            // Only allow max 6 characters
            const limited = text.replace(/[^A-Z0-9]/gi, '').slice(0, 6);
            setCaptchaAnswer(limited);
            const isValid = limited.trim().toUpperCase() === captchaWord.toUpperCase();
            setCaptchaValid(isValid);
            console.log('🔐 Captcha check:', { input: limited.toUpperCase(), expected: captchaWord.toUpperCase(), valid: isValid });
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
                // Auto-submit when 6 digits are entered
                if (code.length === 6) {
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
        onPress={() => {
          console.log('🔘 Register button clicked. isChecked:', isChecked, 'captchaValid:', captchaValid);
          handleRegister();
        }}
        disabled={!isChecked || !captchaValid}
        color="#1D3557"
      />

      {/* Link for users who already have an account */}
      <View style={styles.loginLinkContainer}>
        <Text style={styles.loginLinkText}>I already have an account? </Text>
        <TouchableOpacity onPress={() => router.push('/(tabs)/login')}>
          <Text style={styles.loginLink}>
            Login here
          </Text>
        </TouchableOpacity>
      </View>
      </View>
    </ScrollView>
  );
};

export default Register;