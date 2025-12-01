import React, { useEffect, useRef } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { router } from 'expo-router';

const INACTIVITY_TIMEOUT = 5 * 60 * 1000; // 5 minutes in milliseconds

class InactivityManager {
  private lastActivity: number = Date.now();
  private checkInterval: NodeJS.Timeout | null = null;
  private appStateSubscription: any = null;

  start() {
    this.lastActivity = Date.now();
    
    // Check for inactivity every 30 seconds
    this.checkInterval = setInterval(() => {
      this.checkInactivity();
    }, 30000);

    // Listen to app state changes (foreground/background)
    this.appStateSubscription = AppState.addEventListener('change', this.handleAppStateChange);

    // Track user interactions
    this.setupActivityListeners();
  }

  stop() {
    if (this.checkInterval) {
      clearInterval(this.checkInterval);
      this.checkInterval = null;
    }
    if (this.appStateSubscription) {
      this.appStateSubscription.remove();
    }
  }

  resetActivity() {
    this.lastActivity = Date.now();
  }

  private handleAppStateChange = (nextAppState: AppStateStatus) => {
    if (nextAppState === 'active') {
      // App came to foreground, reset activity
      this.resetActivity();
    }
  };

  private setupActivityListeners() {
    // Reset activity on any touch/interaction
    // This is handled at the app level through touches
  }

  private async checkInactivity() {
    const now = Date.now();
    const inactiveTime = now - this.lastActivity;

    if (inactiveTime >= INACTIVITY_TIMEOUT) {
      console.log('User inactive for 5 minutes - logging out');
      await this.performLogout();
    }
  }

  private async performLogout() {
    try {
      // Clear user data
      await AsyncStorage.removeItem('userData');
      await AsyncStorage.removeItem('userToken');
      
      // Stop the inactivity manager
      this.stop();
      
      // Redirect to login
      router.replace('/(tabs)/login');
    } catch (error) {
      console.error('Error during auto-logout:', error);
    }
  }
}

export const inactivityManager = new InactivityManager();
