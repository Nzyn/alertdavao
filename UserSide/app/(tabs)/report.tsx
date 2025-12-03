import React, { useState, useEffect, useCallback } from 'react';
import {
    View,
    Text,
    ScrollView,
    TextInput,
    Button,
    TouchableOpacity,
    Image,
    Pressable,
    Alert,
    StyleSheet,
    Modal,
    ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useFocusEffect } from 'expo-router';
import * as ImagePicker from 'expo-image-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Platform as RNPlatform } from 'react-native';
// eslint-disable-next-line import/no-unresolved
import { Calendar, CalendarList, Agenda } from 'react-native-calendars';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Conditional WebView import for native platforms only
let WebView: any = null;
if (Platform.OS !== 'web') {
    try {
        const { WebView: RNWebView } = require('react-native-webview');
        WebView = RNWebView;
    } catch (e) {
        console.log('WebView not available on this platform');
    }
}

// reference to avoid unused import warnings for CalendarList and Agenda
(() => { void CalendarList; void Agenda; })();
import EnhancedLocationPicker from '../../components/EnhancedLocationPicker';
import UpdateSuccessDialog from '../../components/UpdateSuccessDialog';
import FlagNotificationToast from '../../components/FlagNotificationToast';
import { reportService } from '../../services/reportService';
import { notificationService, type Notification } from '../../services/notificationService';
import { useUser } from '../../contexts/UserContext';
import { Link } from 'expo-router';
import styles from './styles';
import { validateDavaoLocation } from '../../utils/geofence';

// Helper: get today's date string in Asia/Manila (UTC+8) as YYYY-MM-DD
const manilaTodayStr = () => {
    const now = new Date();
    const utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
    const manila = new Date(utcMs + 8 * 3600000);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${manila.getFullYear()}-${pad(manila.getMonth() + 1)}-${pad(manila.getDate())}`;
};

const CRIME_TYPES = [
    'Theft',
    'Robbery',
    'Burglary',
    'Break-in',
    'Carnapping',
    'Fraud',
    'Cybercrime',
    'Physical Injury',
    'Others',
    'Homicide',
    'Threats',
    'Domestic Violence',
    'Harassment',
    'Sexual Assault',
    'Missing Person',
    'Motornapping',
    'Murder',
    'Rape'
    
];

// ✅ Type for CheckRow props
type CheckRowProps = {
    label: string;
    checked: boolean;
    onToggle: () => void;
};

function CheckRow({ label, checked, onToggle }: CheckRowProps) {
    return (
        <Pressable onPress={onToggle} style={styles.checkboxRow} android_ripple={{ color: '#e5e5e5' }}>
            <View style={[styles.checkboxBox, checked && styles.checkboxBoxChecked]}>
                {checked && <Text style={styles.checkboxTick}>✓</Text>}
            </View>
            <Text style={styles.checkboxText}>{label}</Text>
        </Pressable>
    );
}

export default function ReportCrime() {
    const { user } = useUser();
    const [title, setTitle] = useState('');
    const [titleError, setTitleError] = useState('');
    const [selectedCrimes, setSelectedCrimes] = useState<string[]>([]);
    const [location, setLocation] = useState('');
    const [barangay, setBarangay] = useState('');
    const [barangayId, setBarangayId] = useState<number | null>(null);
    const [streetAddress, setStreetAddress] = useState('');
    const [description, setDescription] = useState('');
    const [selectedDateStr, setSelectedDateStr] = useState<string | null>(null);
    const [timeParts, setTimeParts] = useState<{ hour: number; minute: number; second: number } | null>(null);
    const [hourInput, setHourInput] = useState('');
    const [minuteInput, setMinuteInput] = useState('');
    const [secondInput, setSecondInput] = useState('');
    const [showCalendar, setShowCalendar] = useState(false);
    const [showTimePickerInline, setShowTimePickerInline] = useState(false);
    const [showLocationPicker, setShowLocationPicker] = useState(false);
    const [locationCoordinates, setLocationCoordinates] = useState<{ latitude: number; longitude: number } | null>(null);
    const [isAnonymous, setIsAnonymous] = useState(false);
    const [selectedMedia, setSelectedMedia] = useState<ImagePicker.ImagePickerAsset | null>(null);
    const [showMediaViewer, setShowMediaViewer] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuccessDialog, setShowSuccessDialog] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [flagNotification, setFlagNotification] = useState<Notification | null>(null);
    const [isFlagged, setIsFlagged] = useState(false);
    const [userId, setUserId] = useState<string>('');

    // Load user ID and check flag status when component mounts or comes into focus
    useFocusEffect(
        React.useCallback(() => {
            const loadUserAndCheckFlags = async () => {
                try {
                    const userData = await AsyncStorage.getItem('userData');
                    if (userData) {
                        const parsedUser = JSON.parse(userData);
                        const currentUserId = parsedUser.id || parsedUser.user_id || '';
                        setUserId(currentUserId);
                        
                        // Load notifications to check if user is flagged
                        const notifications = await notificationService.getUserNotifications(currentUserId);
                        
                        // Find the latest user_flagged notification
                        const flaggedNotification = notifications.find(n => n.type === 'user_flagged');
                        
                        if (flaggedNotification) {
                            setFlagNotification(flaggedNotification);
                            setIsFlagged(true);
                            console.log('User is flagged:', flaggedNotification);
                        } else {
                            setIsFlagged(false);
                            setFlagNotification(null);
                        }
                    }
                } catch (error) {
                    console.error('Error loading user flag status:', error);
                }
            };
            
            loadUserAndCheckFlags();
        }, [])
    );

    // Listen for messages from web iframe minimap
    useEffect(() => {
        if (Platform.OS === 'web') {
            const handleMessage = (event: MessageEvent) => {
                if (event.data && event.data.type === 'locationSelected') {
                    const { latitude, longitude } = event.data;
                    
                    // Validate location is within Davao City
                    const validation = validateDavaoLocation(latitude, longitude);
                    if (!validation.isValid) {
                        Alert.alert(
                            'Location Outside Davao City',
                            'We only accept reports within Davao City.\\n\\nThe selected location is outside Davao City boundaries. Please tap on the map within Davao City to proceed with your report.',
                            [
                                {
                                    text: 'OK',
                                    style: 'default'
                                }
                            ]
                        );
                        return; // Don't set invalid location
                    }

                    setLocationCoordinates({
                        latitude,
                        longitude
                    });
                    
                    // Auto-reverse geocode to get address
                    fetch(`http://localhost:3000/api/location/reverse?lat=${latitude}&lon=${longitude}`)
                        .then(res => res.json())
                        .then(addressData => {
                            if (addressData.success) {
                                setStreetAddress(addressData.address || '');
                                setLocation(addressData.barangay || '');
                                setBarangayId(addressData.barangay_id || null);
                            }
                        })
                        .catch(err => console.error('Reverse geocode error:', err));
                }
            };

            window.addEventListener('message', handleMessage);
            return () => window.removeEventListener('message', handleMessage);
        }
    }, []);

    // Debug: Log when showLocationPicker changes
    useEffect(() => {
        console.log('📍 showLocationPicker changed:', showLocationPicker);
    }, [showLocationPicker]);

    // ✅ Toggle function with correct typing
    const toggleCrimeType = (crime: string) => {
        setSelectedCrimes((prev) =>
            prev.includes(crime) ? prev.filter((c) => c !== crime) : [...prev, crime]
        );
    };

    const handleUseLocation = () => {
        console.log('🗺️ Opening location picker...');
        setShowLocationPicker(true);
        console.log('✅ showLocationPicker set to true');
    };

    const handleLocationSelect = (data: {
        barangay: string;
        barangay_id: number;
        street_address: string;
        full_address: string;
        latitude: number;
        longitude: number;
    }) => {
        console.log('📥 handleLocationSelect called in report.tsx');
        console.log('✅ Location data received:', data);
        console.log('📍 Coordinates:', {
            latitude: data.latitude,
            longitude: data.longitude
        });

        // Validate location is within Davao City
        const validation = validateDavaoLocation(data.latitude, data.longitude);
        if (!validation.isValid) {
            Alert.alert(
                'Location Outside Davao City',
                'We only accept reports within Davao City.\n\nThe selected location is outside Davao City boundaries. Please select a different location within Davao City to proceed with your report.',
                [
                    {
                        text: 'Select Different Location',
                        onPress: () => {
                            // Keep the location picker open or reopen it
                            setShowLocationPicker(true);
                        },
                        style: 'default'
                    }
                ],
                { cancelable: false }
            );
            return; // Don't proceed with invalid location
        }

        // Set location display as full address
        setLocation(data.full_address);
        setBarangay(data.barangay);
        setBarangayId(data.barangay_id);
        setStreetAddress(data.street_address);
        setLocationCoordinates({
            latitude: data.latitude,
            longitude: data.longitude
        });
        
        console.log('✅ State updated:', {
            location: data.full_address,
            barangay: data.barangay,
            barangayId: data.barangay_id,
            streetAddress: data.street_address,
            coordinates: {
                latitude: data.latitude,
                longitude: data.longitude
            }
        });
        
        setShowLocationPicker(false);
        console.log('✅ Location picker closed');
    };

    const handleLocationPickerClose = () => {
        setShowLocationPicker(false);
    };

    const pickMedia = async () => {
        // Request permission first
        const permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();

        if (!permissionResult.granted) {
            Alert.alert('Permission Required', 'Please grant access to your photo library to upload evidence.');
            return;
        }

        // Launch image picker
        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ['images', 'videos'],
            allowsEditing: true,
            quality: 1,
        });

        if (!result.canceled && result.assets && result.assets[0]) {
            const asset = result.assets[0];

            // Check file size (25MB = 25 * 1024 * 1024 bytes)
            const maxSizeInBytes = 25 * 1024 * 1024;

            if (asset.fileSize && asset.fileSize > maxSizeInBytes) {
                Alert.alert('File Too Large', 'Your file is too big. Please select a file smaller than 25MB.');
                return;
            }

            setSelectedMedia(asset);
        }
    };

    const removeMedia = () => {
        setSelectedMedia(null);
    };


    const formatIncidentDateTime = (
        dateStr: string | null,
        tp: { hour: number; minute: number; second: number } | null
    ) => {
        if (!dateStr || !tp) return 'Select incident date & time (PST)';
        const pad = (n: number) => n.toString().padStart(2, '0');
        const [yyyy, mmStr, ddStr] = dateStr.split('-');
        const mmNum = parseInt(mmStr, 10);
        const months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        const monthName = months[(mmNum - 1 + 12) % 12];
        const hh = pad(tp.hour);
        const mi = pad(tp.minute);
        const ss = pad(tp.second);
        return `${yyyy}-${mmStr}-${ddStr} ${hh}:${mi}:${ss} (${monthName})`;
    };

    const handleSubmit = async () => {
        console.log('🔍 Validating report submission...');

        // Check if user is flagged
        if (isFlagged) {
            Alert.alert(
                'Account Flagged',
                'Your account has been flagged. You are unable to submit new reports until the flag is lifted by an administrator.',
                [{ text: 'OK' }]
            );
            return;
        }

        console.log('Current state:', {
            title: title.trim(),
            selectedCrimes,
            description: description.trim(),
            selectedDateStr,
            timeParts,
            user,
            barangay,
            barangayId,
            streetAddress: streetAddress.trim(),
            locationCoordinates
        });

        // Validation - Check all required fields
        if (!title.trim()) {
            console.log('❌ Validation failed: No title');
            window.alert('Missing Information: Please enter a title for your report.');
            return;
        }
        console.log('✅ Title validated');

        if (selectedCrimes.length === 0) {
            console.log('❌ Validation failed: No crime types');
            window.alert('Missing Information: Please select at least one crime type.');
            return;
        }
        console.log('✅ Crime types validated');

        if (!description.trim()) {
            console.log('❌ Validation failed: No description');
            window.alert('Missing Information: Please describe what happened in detail.');
            return;
        }
        console.log('✅ Description validated');

        if (!selectedDateStr || !timeParts) {
            console.log('❌ Validation failed: No date/time');
            window.alert('Missing Information: Please specify when the incident occurred (date and time).');
            return;
        }
        console.log('✅ Date/time validated');

        // Check if user is logged in
        console.log('👤 Current user from context:', user);
        console.log('🆔 User ID:', user?.id);

        if (!user || !user.id) {
            console.log('❌ Validation failed: User not logged in');
            window.alert('Authentication Required: You must be logged in to submit a report.');
            console.error('User not logged in:', user);
            return;
        }
        console.log('✅ User authenticated');

        // Street address and coordinates are required, barangay is optional (will be determined server-side)
        if (!streetAddress.trim()) {
            console.log('❌ Validation failed: No street address');
            window.alert(
                'Missing Information: Please enter a street address.\n\nClick "Select Location" to add your street name, building, and house number.'
            );
            return;
        }
        console.log('✅ Street address validated');
        
        if (barangay && barangayId) {
            console.log('✅ Barangay selected:', barangay);
        } else {
            console.log('⚠️ No barangay selected - will be determined server-side from coordinates');
        }

        if (!locationCoordinates) {
            console.log('❌ Validation failed: No coordinates');
            window.alert(
                'Missing Information: Location coordinates are missing. Please use the location picker to set a valid location.'
            );
            return;
        }
        console.log('✅ Location coordinates exist');

        // Validate coordinates are not 0,0
        if (locationCoordinates.latitude === 0 && locationCoordinates.longitude === 0) {
            console.log('❌ Validation failed: Invalid coordinates (0,0)');
            window.alert(
                'Invalid Location: The location has invalid coordinates. Please select a valid location using the map.'
            );
            return;
        }
        console.log('✅ Coordinates validated');

        // Final geofence check - ensure location is within Davao City
        console.log('🚧 Final geofence check for coordinates:', locationCoordinates);
        const geofenceValidation = validateDavaoLocation(
            locationCoordinates.latitude,
            locationCoordinates.longitude
        );
        
        if (!geofenceValidation.isValid) {
            console.log('❌ Final geofence check failed:', geofenceValidation.errorMessage);
            window.alert(
                'We Only Accept Reports Within Davao City\n\n' + 
                (geofenceValidation.errorMessage || 
                'The selected location is outside Davao City boundaries.\n\n' +
                'Please go back and select a location within Davao City to proceed with your report.')
            );
            return;
        }
        console.log('✅ Final geofence check passed - location is within Davao City');

        console.log('✅ All validations passed');
        console.log('📢 About to show confirmation...');

        // Show custom confirmation modal
        setShowConfirmDialog(true);
    };

    const submitReportData = async () => {
        try {
            setIsSubmitting(true);
            console.log('\n' + '='.repeat(50));
            console.log('📤 Starting report submission...');

            // Format the incident date and time
            const pad = (n: number) => n.toString().padStart(2, '0');
            const incidentDateTime = `${selectedDateStr} ${pad(timeParts.hour)}:${pad(timeParts.minute)}:${pad(timeParts.second)}`;

            // Prepare report data
            const reportData = {
                title: title.trim(),
                crimeTypes: selectedCrimes,
                description: description.trim(),
                incidentDate: incidentDateTime,
                isAnonymous,
                latitude: locationCoordinates?.latitude ?? null,
                longitude: locationCoordinates?.longitude ?? null,
                location: location.trim(),
                reporters_address: streetAddress.trim(), // Street address for database
                barangay: barangay, // Barangay name
                barangay_id: barangayId, // Barangay ID for linking
                media: selectedMedia ? {
                    uri: selectedMedia.uri,
                    fileName: selectedMedia.fileName ?? undefined,
                    fileSize: selectedMedia.fileSize ?? undefined,
                    type: selectedMedia.type ?? undefined
                } : null,
                userId: user.id,
            };

            console.log('📋 Report Data:');
            console.log('   Title:', reportData.title);
            console.log('   Crime Types:', reportData.crimeTypes);
            console.log('   Location:', reportData.location);
            console.log('   Coordinates:', { lat: reportData.latitude, lng: reportData.longitude });
            console.log('   Has Media:', !!reportData.media);
            console.log('   Anonymous:', reportData.isAnonymous);
            console.log('   User ID:', reportData.userId);

            // Submit the report
            console.log('\n🚀 Calling reportService.submitReport()...');
            const response = await reportService.submitReport(reportData);

            console.log('✅ Report submitted successfully!');
            console.log('Response:', response);

            // Store success data to show in dialog
            const locationStr = location.trim() || `${locationCoordinates?.latitude?.toFixed(4)}, ${locationCoordinates?.longitude?.toFixed(4)}`;
            const successMessage = `Your report has been submitted successfully.\n\nLocation: ${locationStr}\n\nIP Address and location have been recorded for security and tracking purposes.`;

            // Show success dialog with IP/location recording message
            setShowSuccessDialog(true);

            // Store the message to display
            (global as any).successMessage = successMessage;

            console.log('='.repeat(50) + '\n');
        } catch (error) {
            console.error('\n❌ Error submitting report:', error);
            console.log('='.repeat(50) + '\n');

            const errorMessage = error instanceof Error
                ? error.message
                : 'An unexpected error occurred. Please try again.';

            window.alert('Submission Failed: ' + errorMessage);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <ScrollView
            style={{ flex: 1, backgroundColor: '#fff' }}
            contentContainerStyle={{ paddingBottom: 48 }}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={true}
            scrollEnabled={true}
            nestedScrollEnabled={true}
            bounces={true}
        >
            {/* Flag Notification Toast */}
            <FlagNotificationToast
                notification={flagNotification}
                onDismiss={() => setFlagNotification(null)}
            />
            
            {/* Header with Back Button and Title */}
            <View style={styles.headerHistory}>
                <TouchableOpacity onPress={() => router.push('/')}>
                    <Ionicons name="chevron-back" size={24} color="#000" />
                </TouchableOpacity>
                <View style={{ flex: 1, alignItems: 'center' }}>
                    <Text style={styles.textTitle}>
                        <Text style={styles.alertWelcome}>Alert</Text>
                        <Text style={styles.davao}>Davao</Text>
                    </Text>
                    <Text style={styles.subheadingCenter}>Report Crime</Text>
                </View>
                <View style={{ width: 24 }} />
            </View>

            <Text style={styles.label}>Title *</Text>
            <TextInput
                style={[styles.input, titleError && { borderColor: '#ef4444' }]}
                placeholder="e.g., Wallet stolen near market"
                value={title}
                onChangeText={(text) => {
                    if (text.length <= 255) {
                        setTitle(text);
                        setTitleError('');
                    } else {
                        setTitleError('The title is too long. Please provide a concise version.');
                    }
                }}
                maxLength={255}
            />
            {titleError ? (
                <Text style={{ color: '#ef4444', fontSize: 12, marginTop: 4, marginBottom: 8 }}>
                    ⚠️ {titleError}
                </Text>
            ) : null}
            {title.length > 200 && !titleError ? (
                <Text style={{ color: '#f59e0b', fontSize: 12, marginTop: 4, marginBottom: 8 }}>
                    {255 - title.length} characters remaining
                </Text>
            ) : null}

            <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 12 }}>
                <Text style={styles.subheading}>Select the type of </Text>
                <Link href={{ pathname: "/guidelines", params: { scrollToSection: "crime-types" } }} asChild>
                    <TouchableOpacity>
                        <Text style={[styles.subheading, { color: '#0066cc', textDecorationLine: 'underline' }]}>crimes</Text>
                    </TouchableOpacity>
                </Link>
                <Text style={styles.subheading}> *</Text>
            </View>
            <View style={styles.card}>
                <View style={{ flexDirection: 'row', justifyContent: 'space-between', gap: 16 }}>
                    {/* Left Column */}
                    <View style={{ flex: 1 }}>
                         {CRIME_TYPES.slice(0, 9).map((crime) => (
                            <CheckRow
                                key={crime}
                                label={crime}
                                checked={selectedCrimes.includes(crime)}
                                onToggle={() => toggleCrimeType(crime)}
                            />
                        ))}
                    </View>
                    {/* Right Column */}
                    <View style={{ flex: 1 }}>
                        {CRIME_TYPES.slice(9).map((crime) => (
                            <CheckRow
                                key={crime}
                                label={crime}
                                checked={selectedCrimes.includes(crime)}
                                onToggle={() => toggleCrimeType(crime)}
                            />
                        ))}
                    </View>
                </View>
                {/* Helper Message */}
                <View style={{ marginTop: 12, paddingHorizontal: 12, paddingVertical: 8, backgroundColor: '#f5f5f5', borderRadius: 8, borderLeftWidth: 4, borderLeftColor: '#1D3557' }}>
                    <Text style={{ fontSize: 13, color: '#666', lineHeight: 18 }}>
                        Not sure which category to choose? If you're confused, please check the definitions above for guidance.
                    </Text>
                </View>
            </View>

            <Text style={styles.label}>Location *</Text>

            {/* Location Display */}
            {location && (
                <View style={{
                    backgroundColor: '#f0f7ff',
                    padding: 12,
                    borderRadius: 8,
                    marginBottom: 12,
                    borderWidth: 1,
                    borderColor: '#d0e7ff'
                }}>
                    <Text style={{ fontSize: 14, color: '#1D3557', fontWeight: '600', marginBottom: 4 }}>
                        {location}
                    </Text>
                    {streetAddress && (
                        <Text style={{ fontSize: 13, color: '#666', marginTop: 4 }}>
                            📍 {streetAddress}
                        </Text>
                    )}
                </View>
            )}

            {!location && (
                <Text style={{
                    padding: 12,
                    backgroundColor: '#f5f5f5',
                    borderRadius: 8,
                    color: '#666',
                    fontSize: 14,
                    marginBottom: 12,
                    textAlign: 'center'
                }}>
                    No location selected yet
                </Text>
            )}

            {locationCoordinates && (
                <>
                    <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8, paddingHorizontal: 4 }}>
                        <Ionicons name="checkmark-circle" size={16} color="#4CAF50" />
                        <Text style={{ marginLeft: 6, color: '#4CAF50', fontSize: 12, fontWeight: '500' }}>
                            Coordinates: {locationCoordinates.latitude.toFixed(6)}, {locationCoordinates.longitude.toFixed(6)}
                        </Text>
                    </View>
                    
                    {/* Static Preview */}
                    <View style={{
                        width: '100%',
                        height: 150,
                        borderRadius: 8,
                        overflow: 'hidden',
                        marginBottom: 12,
                        borderWidth: 1,
                        borderColor: '#d0e7ff',
                    }}>
                        <Image
                            source={{
                                uri: `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s+1D3557(${locationCoordinates.longitude},${locationCoordinates.latitude})/${locationCoordinates.longitude},${locationCoordinates.latitude},14,0/350x150@2x?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw`
                            }}
                            style={{ width: '100%', height: '100%' }}
                            resizeMode="cover"
                        />
                        <View style={{
                            position: 'absolute',
                            top: 8,
                            right: 8,
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            paddingHorizontal: 8,
                            paddingVertical: 4,
                            borderRadius: 4,
                        }}>
                            <Text style={{ fontSize: 10, color: '#666', fontWeight: '600' }}>
                                📍 Location Preview
                            </Text>
                        </View>
                    </View>
                </>
            )}

            <TouchableOpacity style={styles.locationButton} onPress={handleUseLocation}>
                <Ionicons name="location" size={18} color="#fff" style={{ marginRight: 8 }} />
                <Text style={styles.locationButtonText}>
                    {location ? 'Change Location' : 'Select Location'}
                </Text>
            </TouchableOpacity>

            <Text style={styles.label}>Description *</Text>
            <TextInput
                style={[styles.input, styles.textArea]}
                placeholder="Describe what happened in detail..."
                value={description}
                onChangeText={setDescription}
                multiline
            />

            <Text style={styles.label}>Photo/Video Evidence (optional)</Text>
            <TouchableOpacity style={styles.mediaButton} onPress={pickMedia}>
                <Ionicons name="camera-outline" size={24} color="#1D3557" />
                <Text style={styles.mediaButtonText}>Select Photo/Video</Text>
            </TouchableOpacity>

            {selectedMedia && (
                <View style={styles.mediaPreviewContainer}>
                    <TouchableOpacity style={{ flex: 1 }} onPress={() => setShowMediaViewer(true)}>
                        <Text style={styles.mediaName}>{selectedMedia.fileName || 'Selected Media'}</Text>
                        <Text style={styles.mediaSize}>
                            {selectedMedia.fileSize ? `${(selectedMedia.fileSize / (1024 * 1024)).toFixed(2)} MB` : 'Unknown size'}
                        </Text>
                        <Text style={{ color: '#1D3557', marginTop: 4 }}>Tap to view</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.removeButton} onPress={removeMedia}>
                        <Ionicons name="close-circle" size={24} color="#dc3545" />
                    </TouchableOpacity>
                </View>
            )}

            {/* Full-screen media viewer */}
            {showMediaViewer && (
                <Modal
                    transparent
                    visible={showMediaViewer}
                    animationType="fade"
                    onRequestClose={() => setShowMediaViewer(false)}
                >
                    <View style={styles.modalOverlay}>
                        <View style={[styles.modalContent, { paddingHorizontal: 16, paddingTop: 16 }]}>
                            <View style={styles.modalHeader}>
                                <Text style={styles.modalTitle}>{selectedMedia?.fileName || 'Preview'}</Text>
                                <TouchableOpacity onPress={() => setShowMediaViewer(false)}>
                                    <Ionicons name="close" size={22} color="#666" />
                                </TouchableOpacity>
                            </View>
                            {selectedMedia?.type?.startsWith('image') ? (
                                <Image
                                    source={{ uri: selectedMedia.uri }}
                                    style={{ width: '100%', height: 400, resizeMode: 'contain' }}
                                />
                            ) : (
                                <View style={{ padding: 16 }}>
                                    <Text style={{ textAlign: 'center', color: '#666' }}>
                                        Preview not available for this file type.
                                    </Text>
                                </View>
                            )}
                        </View>
                    </View>
                </Modal>
            )}

            <Text style={styles.label}>Date of Incident *</Text>

            {/* Date Picker Button */}
            <Pressable
                style={({ pressed }) => [
                    styles.dateButton,
                    (!selectedDateStr || !timeParts) && styles.dateButtonEmpty,
                    pressed && { opacity: 0.7 }
                ]}
                onPress={() => {
                    // Auto-select current date and time if not already selected
                    if (!selectedDateStr || !timeParts) {
                        const now = new Date();
                        const manilaOffset = 8 * 60; // UTC+8 in minutes
                        const localOffset = now.getTimezoneOffset(); // Local offset in minutes
                        const manilaTime = new Date(now.getTime() + (manilaOffset + localOffset) * 60 * 1000);

                        const year = manilaTime.getFullYear();
                        const month = String(manilaTime.getMonth() + 1).padStart(2, '0');
                        const day = String(manilaTime.getDate()).padStart(2, '0');
                        const currentDateStr = `${year}-${month}-${day}`;

                        const currentHour = manilaTime.getHours();
                        const currentMinute = manilaTime.getMinutes();
                        const currentSecond = manilaTime.getSeconds();

                        setSelectedDateStr(currentDateStr);
                        setTimeParts({ hour: currentHour, minute: currentMinute, second: currentSecond });
                        setHourInput(String(currentHour).padStart(2, '0'));
                        setMinuteInput(String(currentMinute).padStart(2, '0'));
                        setSecondInput(String(currentSecond).padStart(2, '0'));
                    }
                    setShowCalendar(true);
                }}
            >
                <Ionicons name="calendar-outline" size={20} color={selectedDateStr && timeParts ? "#000" : "#999"} />
                <Text style={[styles.dateButtonText, { color: selectedDateStr && timeParts ? "#000" : "#999" }, (!selectedDateStr || !timeParts) && styles.dateButtonTextPlaceholder]}>
                    {formatIncidentDateTime(selectedDateStr, timeParts)}
                </Text>
                <Ionicons name="chevron-down" size={16} color="#666" />
            </Pressable>

            {/* Calendar Picker Modal */}
            {showCalendar && (
                <Modal
                    transparent={true}
                    visible={showCalendar}
                    animationType="slide"
                    onRequestClose={() => setShowCalendar(false)}
                >
                    <View style={styles.modalOverlay}>
                        <View style={styles.modalContent}>
                            {/* Modal Header with Cancel and Done */}
                            <View style={styles.modalHeader}>
                                <TouchableOpacity onPress={() => { setShowCalendar(false); setShowTimePickerInline(false); }}>
                                    <Text style={styles.modalCancelText}>Cancel</Text>
                                </TouchableOpacity>
                                <View style={{ flex: 1 }} />
                                <TouchableOpacity onPress={() => {
                                    if (showTimePickerInline && selectedDateStr && timeParts) {
                                        setShowCalendar(false);
                                        setShowTimePickerInline(false);
                                    } else {
                                        setShowCalendar(false);
                                    }
                                }}>
                                    <Text style={styles.modalConfirmText}>Done</Text>
                                </TouchableOpacity>
                            </View>

                            {/* Title Section */}
                            {!showTimePickerInline ? (
                                // Date Picker Title: Simple label
                                <View style={{ paddingHorizontal: 20, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#eee' }}>
                                    <Text style={{ fontSize: 18, fontWeight: '600', color: '#333', textAlign: 'center' }}>
                                        Select Date
                                    </Text>
                                    <Text style={{ fontSize: 14, color: '#666', textAlign: 'center', marginTop: 4 }}>
                                        UTC +8, Philippine Time (PHT)
                                    </Text>
                                </View>
                            ) : (
                                // Time Picker Title: Just the label
                                <View style={{ paddingHorizontal: 20, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#eee' }}>
                                    <Text style={{ fontSize: 18, fontWeight: '600', color: '#333', textAlign: 'center' }}>
                                        Select Time (PST)
                                    </Text>
                                </View>
                            )}

                            {/* Calendar or Time Picker Content */}
                            {!showTimePickerInline ? (
                                <Calendar
                                    current={selectedDateStr || manilaTodayStr()}
                                    minDate={"1900-01-01"}
                                    maxDate={manilaTodayStr()}
                                    enableSwipeMonths={true}
                                    disableAllTouchEventsForDisabledDays={true}
                                    markedDates={selectedDateStr ? { [selectedDateStr]: { selected: true } } : undefined}
                                    hideExtraDays={true}
                                    hideArrows={false}
                                    renderHeader={(date) => {
                                        // Custom header to show only month and year
                                        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                            'July', 'August', 'September', 'October', 'November', 'December'];
                                        const month = monthNames[date.getMonth()];
                                        const year = date.getFullYear();
                                        return (
                                            <View style={{ paddingVertical: 10 }}>
                                                <Text style={{ fontSize: 16, fontWeight: '600', color: '#333', textAlign: 'center' }}>
                                                    {month} {year}
                                                </Text>
                                            </View>
                                        );
                                    }}
                                    theme={{
                                        todayTextColor: '#1D3557',
                                        selectedDayBackgroundColor: '#1D3557',
                                        selectedDayTextColor: '#ffffff',
                                    }}
                                    onDayPress={(day) => {
                                        setSelectedDateStr(day.dateString);
                                        // Initialize time inputs on date selection
                                        setHourInput('00');
                                        setMinuteInput('00');
                                        setSecondInput('00');
                                        setTimeParts({ hour: 0, minute: 0, second: 0 });
                                        // Show time picker inline
                                        setShowTimePickerInline(true);
                                    }}
                                />
                            ) : (
                                <View style={{ paddingVertical: 20, paddingHorizontal: 20, minHeight: 250, backgroundColor: '#fff' }}>
                                    {RNPlatform.OS === 'web' ? (
                                        // Custom web time picker
                                        <View>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-around', marginBottom: 20 }}>
                                                {/* Hour picker */}
                                                <View style={{ alignItems: 'center' }}>
                                                    <Text style={{ fontSize: 14, color: '#666', marginBottom: 8 }}>Hour</Text>
                                                    <TextInput
                                                        style={{ borderWidth: 1, borderColor: '#ccc', borderRadius: 8, padding: 12, width: 70, textAlign: 'center', fontSize: 18 }}
                                                        keyboardType="number-pad"
                                                        maxLength={2}
                                                        placeholder="00"
                                                        value={hourInput}
                                                        onChangeText={(text) => {
                                                            // Store raw input
                                                            setHourInput(text);
                                                            if (text === '') {
                                                                setTimeParts(prev => ({ hour: 0, minute: prev?.minute ?? 0, second: prev?.second ?? 0 }));
                                                                return;
                                                            }
                                                            const hour = parseInt(text, 10);
                                                            if (!isNaN(hour) && hour >= 0 && hour <= 23) {
                                                                setTimeParts(prev => ({ hour, minute: prev?.minute ?? 0, second: prev?.second ?? 0 }));
                                                            }
                                                        }}
                                                        onFocus={() => {
                                                            // Set input to current value for editing
                                                            setHourInput(timeParts?.hour !== undefined ? String(timeParts.hour).padStart(2, '0') : '');
                                                        }}
                                                        onBlur={() => {
                                                            // Format on blur
                                                            if (timeParts?.hour !== undefined) {
                                                                setHourInput(String(timeParts.hour).padStart(2, '0'));
                                                            }
                                                        }}
                                                    />
                                                </View>
                                                {/* Minute picker */}
                                                <View style={{ alignItems: 'center' }}>
                                                    <Text style={{ fontSize: 14, color: '#666', marginBottom: 8 }}>Minute</Text>
                                                    <TextInput
                                                        style={{ borderWidth: 1, borderColor: '#ccc', borderRadius: 8, padding: 12, width: 70, textAlign: 'center', fontSize: 18 }}
                                                        keyboardType="number-pad"
                                                        maxLength={2}
                                                        placeholder="00"
                                                        value={minuteInput}
                                                        onChangeText={(text) => {
                                                            setMinuteInput(text);
                                                            if (text === '') {
                                                                setTimeParts(prev => ({ hour: prev?.hour ?? 0, minute: 0, second: prev?.second ?? 0 }));
                                                                return;
                                                            }
                                                            const minute = parseInt(text, 10);
                                                            if (!isNaN(minute) && minute >= 0 && minute <= 59) {
                                                                setTimeParts(prev => ({ hour: prev?.hour ?? 0, minute, second: prev?.second ?? 0 }));
                                                            }
                                                        }}
                                                        onFocus={() => {
                                                            setMinuteInput(timeParts?.minute !== undefined ? String(timeParts.minute).padStart(2, '0') : '');
                                                        }}
                                                        onBlur={() => {
                                                            if (timeParts?.minute !== undefined) {
                                                                setMinuteInput(String(timeParts.minute).padStart(2, '0'));
                                                            }
                                                        }}
                                                    />
                                                </View>
                                                {/* Second picker */}
                                                <View style={{ alignItems: 'center' }}>
                                                    <Text style={{ fontSize: 14, color: '#666', marginBottom: 8 }}>Second</Text>
                                                    <TextInput
                                                        style={{ borderWidth: 1, borderColor: '#ccc', borderRadius: 8, padding: 12, width: 70, textAlign: 'center', fontSize: 18 }}
                                                        keyboardType="number-pad"
                                                        maxLength={2}
                                                        placeholder="00"
                                                        value={secondInput}
                                                        onChangeText={(text) => {
                                                            setSecondInput(text);
                                                            if (text === '') {
                                                                setTimeParts(prev => ({ hour: prev?.hour ?? 0, minute: prev?.minute ?? 0, second: 0 }));
                                                                return;
                                                            }
                                                            const second = parseInt(text, 10);
                                                            if (!isNaN(second) && second >= 0 && second <= 59) {
                                                                setTimeParts(prev => ({ hour: prev?.hour ?? 0, minute: prev?.minute ?? 0, second }));
                                                            }
                                                        }}
                                                        onFocus={() => {
                                                            setSecondInput(timeParts?.second !== undefined ? String(timeParts.second).padStart(2, '0') : '');
                                                        }}
                                                        onBlur={() => {
                                                            if (timeParts?.second !== undefined) {
                                                                setSecondInput(String(timeParts.second).padStart(2, '0'));
                                                            }
                                                        }}
                                                    />
                                                </View>
                                            </View>
                                            <View style={{ marginTop: 16, alignItems: 'center' }}>
                                                <Text style={{ color: '#666', fontSize: 16, fontWeight: '600' }}>
                                                    {timeParts ? `${String(timeParts.hour).padStart(2, '0')}:${String(timeParts.minute).padStart(2, '0')}:${String(timeParts.second).padStart(2, '0')}` : '12:00:00'}
                                                </Text>
                                                <Text style={{ color: '#999', fontSize: 12, marginTop: 4 }}>24-hour format (PST)</Text>
                                            </View>
                                        </View>
                                    ) : (
                                        // Native time picker for iOS/Android
                                        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
                                            <DateTimePicker
                                                value={new Date(0, 0, 1, timeParts?.hour ?? 12, timeParts?.minute ?? 0, timeParts?.second ?? 0)}
                                                mode="time"
                                                is24Hour={true}
                                                display="spinner"
                                                style={{ width: '100%', height: 200 }}
                                                onChange={(event, selectedDate) => {
                                                    if (selectedDate) {
                                                        setTimeParts({
                                                            hour: selectedDate.getHours(),
                                                            minute: selectedDate.getMinutes(),
                                                            second: selectedDate.getSeconds?.() ?? 0,
                                                        });
                                                    }
                                                }}
                                            />
                                        </View>
                                    )}
                                </View>
                            )}
                        </View>
                    </View>
                </Modal>
            )}

            {/* Confirmation Dialog */}
            <Modal
                visible={showConfirmDialog}
                transparent={true}
                animationType="fade"
                onRequestClose={() => setShowConfirmDialog(false)}
            >
                <View style={confirmStyles.overlay}>
                    <View style={confirmStyles.dialog}>
                        <View style={confirmStyles.iconContainer}>
                            <Ionicons name="shield-checkmark" size={60} color="#1D3557" />
                        </View>

                        <Text style={confirmStyles.title}>Confirm Submission</Text>

                        <Text style={confirmStyles.message}>
                            Your <Text style={confirmStyles.highlight}>IP address</Text> will be recorded for security and tracking purposes.
                        </Text>

                        <Text style={confirmStyles.subMessage}>
                            This helps ensure accountability and assists law enforcement in responding to reports.
                        </Text>

                        <Text style={confirmStyles.question}>Do you want to proceed?</Text>

                        <View style={confirmStyles.buttonContainer}>
                            <TouchableOpacity
                                style={[confirmStyles.button, confirmStyles.cancelButton]}
                                onPress={() => {
                                    console.log('Report submission cancelled by user');
                                    setShowConfirmDialog(false);
                                }}
                            >
                                <Text style={confirmStyles.cancelButtonText}>Cancel</Text>
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={[confirmStyles.button, confirmStyles.submitButton]}
                                onPress={() => {
                                    console.log('🚀 User confirmed submission, calling submitReportData...');
                                    setShowConfirmDialog(false);
                                    submitReportData();
                                }}
                            >
                                <Ionicons name="checkmark-circle" size={20} color="#fff" style={{ marginRight: 8 }} />
                                <Text style={confirmStyles.submitButtonText}>Submit Report</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* Location Picker Modal */}
            {showLocationPicker && (
                <EnhancedLocationPicker
                    visible={showLocationPicker}
                    onClose={handleLocationPickerClose}
                    onLocationSelect={handleLocationSelect}
                />
            )}

            <CheckRow
                label="Report Anonymously"
                checked={isAnonymous}
                onToggle={() => setIsAnonymous((v) => !v)}
            />

            <View style={styles.submitButton}>
                <Button
                    title={isFlagged ? "Account Flagged - Cannot Submit" : (isSubmitting ? "Submitting..." : "Submit Report")}
                    onPress={handleSubmit}
                    color={isFlagged ? "#999" : "#1D3557"}
                    disabled={isSubmitting || isFlagged}
                />
            </View>
            
            {isFlagged && (
                <View style={{
                    backgroundColor: '#fee2e2',
                    borderLeftWidth: 4,
                    borderLeftColor: '#dc2626',
                    padding: 12,
                    marginTop: 12,
                    marginHorizontal: 12,
                    borderRadius: 6,
                }}>
                    <View style={{ flexDirection: 'row', alignItems: 'flex-start', gap: 8 }}>
                        <Ionicons name="warning" size={18} color="#dc2626" style={{ marginTop: 2 }} />
                        <View style={{ flex: 1 }}>
                            <Text style={{ fontSize: 14, fontWeight: '600', color: '#991b1b', marginBottom: 4 }}>
                                Account Flagged
                            </Text>
                            <Text style={{ fontSize: 13, color: '#7f1d1d', lineHeight: 18 }}>
                                Your account has been flagged. You are unable to submit new reports until the flag is lifted by an administrator.
                            </Text>
                        </View>
                    </View>
                </View>
            )}

            {isSubmitting && (
                <View style={{ alignItems: 'center', marginTop: 16, marginBottom: 16 }}>
                    <ActivityIndicator size="large" color="#1D3557" />
                    <Text style={{ marginTop: 8, color: '#666' }}>Submitting your report...</Text>
                </View>
            )}

            {/* Success Dialog */}
            {showSuccessDialog && (
                <UpdateSuccessDialog
                    visible={showSuccessDialog}
                    title="Report Submitted!"
                    message="Your report has been submitted successfully. Thank you for helping make our community safer."
                    okText="View History"
                    onOk={() => {
                        setShowSuccessDialog(false);
                        // Reset form
                        setTitle('');
                        setSelectedCrimes([]);
                        setLocation('');
                        setBarangay('');
                        setBarangayId(null);
                        setStreetAddress('');
                        setDescription('');
                        setSelectedDateStr(null);
                        setTimeParts(null);
                        setLocationCoordinates(null);
                        setIsAnonymous(false);
                        setSelectedMedia(null);
                        // Navigate to history
                        router.push('/history');
                    }}
                />
            )}
        </ScrollView>
    );
}

const confirmStyles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    dialog: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 24,
        width: '100%',
        maxWidth: 440,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 8,
    },
    iconContainer: {
        alignItems: 'center',
        marginBottom: 16,
    },
    title: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#1D3557',
        textAlign: 'center',
        marginBottom: 16,
    },
    message: {
        fontSize: 16,
        color: '#333',
        textAlign: 'center',
        lineHeight: 24,
        marginBottom: 12,
    },
    highlight: {
        fontWeight: '700',
        color: '#1D3557',
    },
    subMessage: {
        fontSize: 14,
        color: '#666',
        textAlign: 'center',
        lineHeight: 20,
        marginBottom: 20,
        fontStyle: 'italic',
    },
    question: {
        fontSize: 16,
        fontWeight: '600',
        color: '#1D3557',
        textAlign: 'center',
        marginBottom: 24,
    },
    buttonContainer: {
        flexDirection: 'row',
        gap: 12,
    },
    button: {
        flex: 1,
        paddingVertical: 14,
        paddingHorizontal: 20,
        borderRadius: 8,
        alignItems: 'center',
        justifyContent: 'center',
        flexDirection: 'row',
    },
    cancelButton: {
        backgroundColor: '#f1f3f5',
        borderWidth: 1,
        borderColor: '#ddd',
    },
    cancelButtonText: {
        color: '#666',
        fontSize: 16,
        fontWeight: '600',
    },
    submitButton: {
        backgroundColor: '#1D3557',
        shadowColor: '#1D3557',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.3,
        shadowRadius: 4,
        elevation: 4,
    },
    submitButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '700',
    },
});
