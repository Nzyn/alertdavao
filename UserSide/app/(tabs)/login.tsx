import React, { useState, useEffect } from "react";
import { View, Text, TextInput, Button, Alert, ScrollView, Pressable } from "react-native";
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from "expo-router";
import { useUser } from '../../contexts/UserContext';
import { BACKEND_URL } from '../../config/backend';
import CaptchaObfuscated, { generateCaptchaWord } from '../../components/CaptchaObfuscated';
import { useGoogleAuth, getGoogleUserInfo } from '../../config/googleAuth';
import * as Google from 'expo-auth-session/providers/google';
import styles from "./styles";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [captchaWord, setCaptchaWord] = useState("");
  const [captchaInput, setCaptchaInput] = useState("");
  const [emailError, setEmailError] = useState("");
  const [passwordError, setPasswordError] = useState("");
  const [captchaError, setCaptchaError] = useState("");
  const { setUser } = useUser();
  const router = useRouter();
  const { request, response, promptAsync } = useGoogleAuth();

  useEffect(() => {
    setCaptchaWord(generateCaptchaWord(6));
  }, []);

  useEffect(() => {
    if (response?.type === 'success') {
      const { authentication } = response;
      if (authentication?.accessToken) {
        handleGoogleSignIn(authentication.accessToken);
      }
    }
  }, [response]);

  const handleGoogleSignIn = async (accessToken: string) => {
    setIsLoading(true);
    try {
      const userInfo = await getGoogleUserInfo(accessToken);
      if (!userInfo) {
        Alert.alert('Error', 'Failed to get user information from Google');
        setIsLoading(false);
        return;
      }

      const response = await fetch(`${BACKEND_URL}/google-login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: userInfo.email,
          firstName: userInfo.given_name,
          lastName: userInfo.family_name,
          profileImage: userInfo.picture,
        }),
      });

      const data = await response.json();
      if (response.ok) {
        const user = data.user || data;
        await AsyncStorage.setItem('userData', JSON.stringify(user));
        setUser({
          id: user.id?.toString() || '0',
          firstName: user.firstname || user.firstName || '',
          lastName: user.lastname || user.lastName || '',
          email: user.email || '',
          phone: user.contact || user.phone || '',
          address: user.address || '',
          isVerified: Boolean(user.is_verified || user.isVerified),
          profileImage: user.profile_image || user.profileImage,
        });
        router.replace('/(tabs)');
      } else {
        Alert.alert('Login Failed', data.message || 'Google login failed');
        setIsLoading(false);
      }
    } catch (err: any) {
      Alert.alert('Network Error', err.message || 'Unknown error');
      setIsLoading(false);
    }
  };

  const refreshCaptcha = () => {
    setCaptchaWord(generateCaptchaWord(6));
    setCaptchaInput("");
  };

  const handleLogin = async () => {
    setEmailError("");
    setPasswordError("");
    setCaptchaError("");
    
    if (!email || !password) {
      Alert.alert('Error', 'Please enter email and password');
      return;
    }
    
    // Verify captcha
    if (captchaInput.toUpperCase() !== captchaWord.toUpperCase()) {
      setCaptchaError('Incorrect captcha. Please try again.');
      refreshCaptcha();
      return;
    }
    
    setIsLoading(true);
    console.log('🔑 Starting login for:', email);
    try {
      const response = await fetch(`${BACKEND_URL}/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });
      const data = await response.json();
      console.log('📥 Login response:', data);
      if (response.ok) {
        // Direct login - no OTP required for sign-in
        // OTP is only used during registration for phone verification
        const user = data.user || data;
        if (user.role === 'police' || user.role === 'admin') {
          Alert.alert('Error', 'Police and Admin users must log in through the AdminSide dashboard.');
          setIsLoading(false);
          return;
        }
        console.log('✅ Login successful for:', user.email);
        await AsyncStorage.setItem('userData', JSON.stringify(user));
        setUser({
          id: user.id?.toString() || '0',
          firstName: user.firstname || user.firstName || '',
          lastName: user.lastname || user.lastName || '',
          email: user.email || '',
          phone: user.contact || user.phone || '',
          address: user.address || '',
          isVerified: Boolean(user.is_verified || user.isVerified),
          profileImage: user.profile_image || user.profileImage,
        });
        router.replace('/(tabs)');
      } else {
        // Display error messages under relevant input fields
        const errorMessage = data.message || 'Login failed';
        const lowerError = errorMessage.toLowerCase();
        
        if (lowerError.includes('user') || lowerError.includes('email') || lowerError.includes('not found')) {
          setEmailError(errorMessage);
        } else if (lowerError.includes('password') || lowerError.includes('incorrect') || lowerError.includes('invalid')) {
          setPasswordError(errorMessage);
        } else {
          Alert.alert('Login Failed', errorMessage);
        }
        setIsLoading(false);
      }
    } catch (err: any) {
      Alert.alert('Network Error', err.message || 'Unknown error');
      setIsLoading(false);
    }
  };

  const handleForgotPassword = () => {
    console.log("Forgot Password clicked!");
  };

  const handleSignUp = () => {
    router.push('/(tabs)/register');
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={{ paddingTop: 20, paddingBottom: 150, alignItems: 'center' }}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={true}
    >
      <View style={{ width: '100%', maxWidth: 440, paddingHorizontal: 20 }}>
        <Text style={styles.textTitle}>
          <Text style={styles.alertWelcome}>Alert</Text>
          <Text style={styles.davao}>Davao</Text>
        </Text>

        <Text style={styles.subheadingCenter}>Welcome back to AlertDavao!</Text>
        <Text style={styles.normalTxtCentered}>Sign in to your account</Text>

        <Text style={styles.subheading2}>Email</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter your email"
          value={email}
          onChangeText={(text) => {
            setEmail(text);
            setEmailError("");
          }}
          keyboardType="email-address"
        />
        {emailError ? (
          <Text style={{ color: '#E63946', fontSize: 12, marginBottom: 10, marginTop: -10 }}>
            {emailError}
          </Text>
        ) : null}

        <Text style={styles.subheading2}>Password</Text>
        <View style={{ position: 'relative' }}>
          <TextInput
            style={[styles.input, { paddingRight: 50 }]}
            placeholder="Enter your password"
            value={password}
            onChangeText={(text) => {
              setPassword(text);
              setPasswordError("");
            }}
            secureTextEntry={!showPassword}
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
        {passwordError ? (
          <Text style={{ color: '#E63946', fontSize: 12, marginBottom: 10, marginTop: -10 }}>
            {passwordError}
          </Text>
        ) : null}

        {/* Captcha Section */}
        <Text style={styles.subheading2}>Captcha Verification</Text>
        <View style={{ marginBottom: 16 }}>
          <CaptchaObfuscated word={captchaWord} />
          <TextInput
            style={styles.input}
            placeholder="Enter captcha above"
            value={captchaInput}
            onChangeText={(text) => {
              const limited = text.replace(/[^A-Z0-9]/gi, '').slice(0, 6).toUpperCase();
              setCaptchaInput(limited);
              setCaptchaError("");
            }}
            autoCapitalize="characters"
            maxLength={6}
          />
          {captchaError ? (
            <Text style={{ color: '#E63946', fontSize: 12, marginBottom: 10, marginTop: -10 }}>
              {captchaError}
            </Text>
          ) : null}
          <Pressable onPress={refreshCaptcha}>
            <Text style={{ color: '#1D3557', textAlign: 'center', marginTop: 4, fontSize: 13 }}>
              🔄 Refresh Captcha
            </Text>
          </Pressable>
        </View>

        <Button
          title={isLoading ? "Logging in..." : "Login"}
          onPress={handleLogin}
          disabled={isLoading || captchaInput.toUpperCase() !== captchaWord.toUpperCase()}
          color={captchaInput.toUpperCase() === captchaWord.toUpperCase() ? "#1D3557" : "#999"}
        />

        <Text onPress={handleForgotPassword} style={{ color: '#1D3557', textAlign: 'center', marginTop: 10 }}>
          Forgot Password?
        </Text>

        <Text style={styles.normalTxtCentered}>
          Don't have an account?{' '}
          <Text onPress={handleSignUp} style={{ color: '#1D3557', fontWeight: '600' }}>
            Sign Up
          </Text>
        </Text>

        {/* Google Sign-In Section */}
        <Text style={{ textAlign: 'center', color: '#999', marginTop: 20, marginBottom: 10 }}>
          or continue with
        </Text>

        <Pressable
          onPress={() => promptAsync()}
          disabled={!request || isLoading}
          style={{
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'center',
            backgroundColor: '#fff',
            borderWidth: 1,
            borderColor: '#ddd',
            borderRadius: 8,
            paddingVertical: 12,
            paddingHorizontal: 16,
            marginBottom: 20,
          }}
        >
          <Text style={{ fontSize: 16, color: '#333', fontWeight: '500' }}>
            🔐 Sign in with Google
          </Text>
        </Pressable>
      </View>
    </ScrollView>
  );
};

export default Login;
