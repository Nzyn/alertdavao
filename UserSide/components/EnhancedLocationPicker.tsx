import React, { useState, useEffect, useRef } from 'react';
import {
    Modal,
    View,
    Text,
    TextInput,
    TouchableOpacity,
    ScrollView,
    Platform,
    Alert,
    StyleSheet,
    Pressable,
    ActivityIndicator,
    FlatList,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';

// Determine backend URL based on platform and environment
const getBackendURL = () => {
    if (Platform.OS === 'web') {
        return 'http://localhost:3000';
    }
    // For native (iOS/Android), try localhost first, fallback to IP
    return 'http://192.168.1.11:3000';
};

const BACKEND_URL = getBackendURL();

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

interface Barangay {
    barangay_id: number;
    barangay_name: string;
    latitude: number;
    longitude: number;
    station_id: number;
}

interface AddressSuggestion {
    display_name: string;
    lat: string;
    lon: string;
}

interface LocationData {
    barangay: string;
    barangay_id: number;
    street_address: string;
    full_address: string;
    latitude: number;
    longitude: number;
}

interface EnhancedLocationPickerProps {
    visible: boolean;
    onClose: () => void;
    onLocationSelect: (data: LocationData) => void;
}

const EnhancedLocationPicker: React.FC<EnhancedLocationPickerProps> = ({
    visible,
    onClose,
    onLocationSelect,
}) => {
    const [barangays, setBarangays] = useState<Barangay[]>([]);
    const [selectedBarangay, setSelectedBarangay] = useState<Barangay | null>(null);
    const [streetAddress, setStreetAddress] = useState('');
    const [showBarangayDropdown, setShowBarangayDropdown] = useState(false);
    const [barangaySearch, setBarangaySearch] = useState('');
    const [addressSuggestions, setAddressSuggestions] = useState<AddressSuggestion[]>([]);
    const [showAddressSuggestions, setShowAddressSuggestions] = useState(false);
    const [mapCoordinates, setMapCoordinates] = useState<{ latitude: number; longitude: number }>({
        latitude: 7.0731,
        longitude: 125.6128,
    });
    const [loading, setLoading] = useState(false);
    const [loadingLocation, setLoadingLocation] = useState(false);
    const [loadingBarangays, setLoadingBarangays] = useState(false);
    const [barangaysError, setBarangaysError] = useState<string | null>(null);
    const addressTimeoutRef = useRef<any>(null);
    const webViewRef = useRef<any>(null);

    // Fetch barangays on mount
    useEffect(() => {
        if (visible) {
            fetchBarangays();
        }
    }, [visible]);

    const fetchBarangays = async () => {
        try {
            console.log('🔄 Fetching barangays from:', `${BACKEND_URL}/api/barangays`);
            setLoadingBarangays(true);
            setBarangaysError(null);

            const response = await fetch(`${BACKEND_URL}/api/barangays`);
            console.log('📡 Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('✅ Barangays received:', data);

                if (data.success && data.data && Array.isArray(data.data)) {
                    console.log('📍 Total barangays loaded:', data.data.length);
                    setBarangays(data.data);
                    setBarangaysError(null);
                } else {
                    console.error('❌ Invalid response format:', data);
                    setBarangaysError('Invalid data format received');
                    Alert.alert('Error', 'Failed to load barangays: Invalid response format');
                }
            } else {
                console.error('❌ Request failed with status:', response.status);
                const errorText = await response.text();
                console.error('Error response:', errorText);
                setBarangaysError(`Server error: ${response.status}`);
                Alert.alert('Error', `Failed to load barangays: Server error ${response.status}`);
            }
        } catch (error) {
            console.error('❌ Error fetching barangays:', error);
            const errorMsg = error instanceof Error ? error.message : 'Unknown error';
            setBarangaysError(errorMsg);
            Alert.alert('Error', `Failed to load barangays: ${errorMsg}`);
        } finally {
            setLoadingBarangays(false);
        }
    };

    // Handle barangay selection
    const handleBarangaySelect = (barangay: Barangay) => {
        setSelectedBarangay(barangay);
        setBarangaySearch(barangay.barangay_name);
        setShowBarangayDropdown(false);

        // Update map to barangay center
        setMapCoordinates({
            latitude: barangay.latitude,
            longitude: barangay.longitude,
        });
        updateMapLocation(barangay.latitude, barangay.longitude);
    };

    // Filter barangays based on search
    const filteredBarangays = barangays.filter(b =>
        b.barangay_name.toLowerCase().includes(barangaySearch.toLowerCase())
    );

    // Handle street address input with autocomplete
    const handleStreetAddressChange = (text: string) => {
        setStreetAddress(text);

        // Clear previous timeout
        if (addressTimeoutRef.current) {
            clearTimeout(addressTimeoutRef.current);
        }

        // Set new timeout for autocomplete
        if (text.length > 2) {
            addressTimeoutRef.current = setTimeout(() => {
                searchAddressSuggestions(text);
            }, 500);
        } else {
            setAddressSuggestions([]);
            setShowAddressSuggestions(false);
        }
    };

    // Search address suggestions
    const searchAddressSuggestions = async (query: string) => {
        try {
            setLoading(true);
            const fullQuery = selectedBarangay
                ? `${query}, ${selectedBarangay.barangay_name}, Davao City, Philippines`
                : `${query}, Davao City, Philippines`;

            const response = await fetch(
                `${BACKEND_URL}/api/location/search?q=${encodeURIComponent(fullQuery)}`
            );

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.results && data.results.length > 0) {
                    setAddressSuggestions(data.results.slice(0, 5));
                    setShowAddressSuggestions(true);
                }
            }
        } catch (error) {
            console.error('Error searching addresses:', error);
        } finally {
            setLoading(false);
        }
    };

    // Handle address suggestion selection
    const handleAddressSuggestionSelect = (suggestion: AddressSuggestion) => {
        setStreetAddress(suggestion.display_name);
        setShowAddressSuggestions(false);

        const lat = parseFloat(suggestion.lat);
        const lon = parseFloat(suggestion.lon);

        setMapCoordinates({ latitude: lat, longitude: lon });
        updateMapLocation(lat, lon);

        // If no barangay selected, try to determine it from coordinates
        if (!selectedBarangay) {
            determineBarangayFromCoordinates(lat, lon);
        }
    };

    // Determine barangay from coordinates using geofencing
    const determineBarangayFromCoordinates = async (lat: number, lon: number) => {
        try {
            const response = await fetch(
                `${BACKEND_URL}/api/barangay/by-coordinates?latitude=${lat}&longitude=${lon}`
            );

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.barangay) {
                    const barangay = barangays.find(b => b.barangay_id === data.barangay.barangay_id);
                    if (barangay) {
                        setSelectedBarangay(barangay);
                        setBarangaySearch(barangay.barangay_name);
                    }
                }
            }
        } catch (error) {
            console.error('Error determining barangay:', error);
        }
    };

    // Use current location
    const handleUseCurrentLocation = async () => {
        try {
            setLoadingLocation(true);

            const { status } = await Location.requestForegroundPermissionsAsync();
            if (status !== 'granted') {
                Alert.alert('Permission Denied', 'Please grant location permission to use this feature.');
                return;
            }

            const location = await Location.getCurrentPositionAsync({
                accuracy: Location.Accuracy.Balanced,
            });

            const { latitude, longitude } = location.coords;
            setMapCoordinates({ latitude, longitude });
            updateMapLocation(latitude, longitude);

            // Reverse geocode to get address
            const response = await fetch(
                `${BACKEND_URL}/api/location/reverse?lat=${latitude}&lon=${longitude}`
            );

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.address) {
                    setStreetAddress(data.address);
                }
            }

            // Determine barangay from coordinates
            await determineBarangayFromCoordinates(latitude, longitude);

        } catch (error) {
            console.error('Error getting current location:', error);
            Alert.alert('Error', 'Failed to get current location. Please try again.');
        } finally {
            setLoadingLocation(false);
        }
    };

    // Update map location
    const updateMapLocation = (lat: number, lon: number) => {
        if (Platform.OS === 'web') {
            const iframe = document.getElementById('location-map-iframe') as HTMLIFrameElement;
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage(
                    { type: 'updateLocation', latitude: lat, longitude: lon },
                    '*'
                );
            }
        } else if (webViewRef.current) {
            webViewRef.current.postMessage(
                JSON.stringify({ type: 'updateLocation', latitude: lat, longitude: lon })
            );
        }
    };

    // Handle map click
    useEffect(() => {
        if (Platform.OS === 'web') {
            const handleMessage = (event: MessageEvent) => {
                if (event.data && event.data.type === 'locationSelected') {
                    const { latitude, longitude } = event.data;
                    setMapCoordinates({ latitude, longitude });

                    // Reverse geocode
                    fetch(`${BACKEND_URL}/api/location/reverse?lat=${latitude}&lon=${longitude}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.address) {
                                setStreetAddress(data.address);
                            }
                        });

                    // Determine barangay
                    determineBarangayFromCoordinates(latitude, longitude);
                }
            };

            window.addEventListener('message', handleMessage);
            return () => window.removeEventListener('message', handleMessage);
        }
    }, [barangays]);

    // Handle confirm
    const handleConfirm = () => {
        if (!selectedBarangay) {
            Alert.alert('Missing Information', 'Please select a barangay.');
            return;
        }

        if (!streetAddress.trim()) {
            Alert.alert('Missing Information', 'Please enter a street address.');
            return;
        }

        const fullAddress = `Mindanao, Davao Del Sur, Davao City, ${selectedBarangay.barangay_name}`;

        onLocationSelect({
            barangay: selectedBarangay.barangay_name,
            barangay_id: selectedBarangay.barangay_id,
            street_address: streetAddress,
            full_address: fullAddress,
            latitude: mapCoordinates.latitude,
            longitude: mapCoordinates.longitude,
        });

        handleClose();
    };

    const handleClose = () => {
        setSelectedBarangay(null);
        setBarangaySearch('');
        setStreetAddress('');
        setAddressSuggestions([]);
        setShowBarangayDropdown(false);
        setShowAddressSuggestions(false);
        onClose();
    };

    const mapHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <style>
                body { margin: 0; padding: 0; }
                #map { width: 100%; height: 100vh; }
            </style>
        </head>
        <body>
            <div id="map"></div>
            <script>
                var map = L.map('map').setView([${mapCoordinates.latitude}, ${mapCoordinates.longitude}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                var marker = L.marker([${mapCoordinates.latitude}, ${mapCoordinates.longitude}], {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function(e) {
                    var pos = marker.getLatLng();
                    window.parent.postMessage({
                        type: 'locationSelected',
                        latitude: pos.lat,
                        longitude: pos.lng
                    }, '*');
                });

                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    window.parent.postMessage({
                        type: 'locationSelected',
                        latitude: e.latlng.lat,
                        longitude: e.latlng.lng
                    }, '*');
                });

                window.addEventListener('message', function(event) {
                    if (event.data.type === 'updateLocation') {
                        var newPos = [event.data.latitude, event.data.longitude];
                        marker.setLatLng(newPos);
                        map.setView(newPos, 15);
                    }
                });
            </script>
        </body>
        </html>
    `;

    return (
        <Modal visible={visible} animationType="slide" onRequestClose={handleClose}>
            <View style={localStyles.container}>
                {/* Header */}
                <View style={localStyles.header}>
                    <TouchableOpacity onPress={handleClose}>
                        <Ionicons name="close" size={28} color="#333" />
                    </TouchableOpacity>
                    <Text style={localStyles.headerTitle}>Select Location</Text>
                    <View style={{ width: 28 }} />
                </View>

                <ScrollView style={localStyles.content}>
                    {/* Location Display */}
                    <View style={localStyles.locationDisplay}>
                        <Text style={localStyles.locationText}>
                            Mindanao, Davao Del Sur, Davao City, {selectedBarangay?.barangay_name || '(Select Barangay)'}
                        </Text>
                    </View>

                    {/* Barangay Selector */}
                    <View style={localStyles.section}>
                        <Text style={localStyles.label}>Barangay * {loadingBarangays && '⏳'}</Text>
                        <TouchableOpacity
                            style={localStyles.dropdown}
                            onPress={() => setShowBarangayDropdown(!showBarangayDropdown)}
                            disabled={loadingBarangays}
                        >
                            <TextInput
                                style={localStyles.dropdownInput}
                                placeholder="Search or select barangay..."
                                value={barangaySearch}
                                onChangeText={setBarangaySearch}
                                onFocus={() => setShowBarangayDropdown(true)}
                                editable={!loadingBarangays}
                            />
                            {loadingBarangays ? (
                                <ActivityIndicator size="small" color="#1D3557" />
                            ) : (
                                <Ionicons name="chevron-down" size={20} color="#666" />
                            )}
                        </TouchableOpacity>

                        {/* Error Message */}
                        {barangaysError && (
                            <View style={{
                                backgroundColor: '#ffebee',
                                padding: 10,
                                borderRadius: 6,
                                marginTop: 8,
                                borderLeftWidth: 3,
                                borderLeftColor: '#d32f2f'
                            }}>
                                <Text style={{ color: '#d32f2f', fontSize: 12, fontWeight: '500' }}>
                                    ❌ {barangaysError}
                                </Text>
                            </View>
                        )}

                        {/* Loading State */}
                        {loadingBarangays && barangays.length === 0 && (
                            <View style={localStyles.dropdownList}>
                                <View style={{ padding: 20, alignItems: 'center' }}>
                                    <ActivityIndicator size="large" color="#1D3557" />
                                    <Text style={{ marginTop: 12, color: '#666', fontSize: 14 }}>
                                        Loading barangays...
                                    </Text>
                                </View>
                            </View>
                        )}

                        {/* Dropdown List */}
                        {showBarangayDropdown && !loadingBarangays && barangays.length > 0 && (
                            <View style={localStyles.dropdownList}>
                                <ScrollView style={{ maxHeight: 300 }} nestedScrollEnabled>
                                    {filteredBarangays.length > 0 ? (
                                        filteredBarangays.map((item) => (
                                            <TouchableOpacity
                                                key={item.barangay_id.toString()}
                                                style={[
                                                    localStyles.dropdownItem,
                                                    selectedBarangay?.barangay_id === item.barangay_id && {
                                                        backgroundColor: '#e3f2fd'
                                                    }
                                                ]}
                                                onPress={() => handleBarangaySelect(item)}
                                            >
                                                <Text style={localStyles.dropdownItemText}>
                                                    {selectedBarangay?.barangay_id === item.barangay_id ? '✓ ' : ''}
                                                    {item.barangay_name}
                                                </Text>
                                            </TouchableOpacity>
                                        ))
                                    ) : (
                                        <View style={{ padding: 12 }}>
                                            <Text style={{ color: '#999', textAlign: 'center', fontSize: 14 }}>
                                                No barangay matches "{barangaySearch}"
                                            </Text>
                                        </View>
                                    )}
                                </ScrollView>
                                {/* Footer showing total */}
                                <View style={{
                                    padding: 8,
                                    borderTopWidth: 1,
                                    borderTopColor: '#eee',
                                    backgroundColor: '#f9f9f9',
                                    alignItems: 'center'
                                }}>
                                    <Text style={{ fontSize: 12, color: '#999' }}>
                                        Showing {filteredBarangays.length} of {barangays.length} barangays
                                    </Text>
                                </View>
                            </View>
                        )}

                        {/* No Results Message */}
                        {showBarangayDropdown && !loadingBarangays && barangays.length === 0 && !barangaysError && (
                            <View style={localStyles.dropdownList}>
                                <View style={{ padding: 20, alignItems: 'center' }}>
                                    <Text style={{ color: '#999', fontSize: 14 }}>
                                        No barangays available
                                    </Text>
                                </View>
                            </View>
                        )}

                        {/* Info when no dropdown shown */}
                        {!showBarangayDropdown && barangays.length > 0 && selectedBarangay && (
                            <Text style={{ fontSize: 12, color: '#4CAF50', marginTop: 4, fontWeight: '500' }}>
                                ✓ {selectedBarangay.barangay_name} selected
                            </Text>
                        )}
                    </View>

                    {/* Street Address */}
                    <View style={localStyles.section}>
                        <Text style={localStyles.label}>Street Name, Building and House No. *</Text>
                        <TextInput
                            style={localStyles.input}
                            placeholder="e.g., Silver Right Street Marfori, San Rafael Village"
                            value={streetAddress}
                            onChangeText={handleStreetAddressChange}
                            multiline
                        />

                        {loading && <ActivityIndicator size="small" color="#1D3557" style={{ marginTop: 8 }} />}

                        {showAddressSuggestions && addressSuggestions.length > 0 && (
                            <View style={localStyles.suggestions}>
                                {addressSuggestions.map((suggestion, index) => (
                                    <TouchableOpacity
                                        key={index}
                                        style={localStyles.suggestionItem}
                                        onPress={() => handleAddressSuggestionSelect(suggestion)}
                                    >
                                        <Ionicons name="location-outline" size={16} color="#666" />
                                        <Text style={localStyles.suggestionText}>{suggestion.display_name}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                        )}
                    </View>

                    {/* Use Current Location Button */}
                    <TouchableOpacity
                        style={localStyles.locationButton}
                        onPress={handleUseCurrentLocation}
                        disabled={loadingLocation}
                    >
                        {loadingLocation ? (
                            <ActivityIndicator size="small" color="#fff" />
                        ) : (
                            <>
                                <Ionicons name="locate" size={20} color="#fff" />
                                <Text style={localStyles.locationButtonText}>Use My Current Location</Text>
                            </>
                        )}
                    </TouchableOpacity>

                    {/* Map */}
                    <View style={localStyles.mapContainer}>
                        <Text style={localStyles.mapLabel}>Drag the marker to adjust location</Text>
                        {Platform.OS === 'web' ? (
                            <iframe
                                id="location-map-iframe"
                                srcDoc={mapHtml}
                                style={{ width: '100%', height: 300, border: 'none', borderRadius: 8 }}
                            />
                        ) : (
                            WebView && (
                                <WebView
                                    ref={webViewRef}
                                    source={{ html: mapHtml }}
                                    style={{ width: '100%', height: 300, borderRadius: 8 }}
                                    onMessage={(event: any) => {
                                        const data = JSON.parse(event.nativeEvent.data);
                                        if (data.type === 'locationSelected') {
                                            const { latitude, longitude } = data;
                                            setMapCoordinates({ latitude, longitude });

                                            // Reverse geocode to get street address
                                            fetch(`${BACKEND_URL}/api/location/reverse?lat=${latitude}&lon=${longitude}`)
                                                .then(res => res.json())
                                                .then(addressData => {
                                                    if (addressData.success && addressData.address) {
                                                        setStreetAddress(addressData.address);
                                                    }
                                                })
                                                .catch(err => console.error('Reverse geocode error:', err));

                                            // Determine barangay
                                            determineBarangayFromCoordinates(latitude, longitude);
                                        }
                                    }}
                                />
                            )
                        )}
                    </View>

                    {/* Coordinates Display */}
                    <View style={localStyles.coordsDisplay}>
                        <Text style={localStyles.coordsText}>
                            Latitude: {mapCoordinates.latitude.toFixed(6)} | Longitude: {mapCoordinates.longitude.toFixed(6)}
                        </Text>
                    </View>
                </ScrollView>

                {/* Confirm Button */}
                <View style={localStyles.footer}>
                    <TouchableOpacity style={localStyles.confirmButton} onPress={handleConfirm}>
                        <Text style={localStyles.confirmButtonText}>Confirm Location</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </Modal>
    );
};

const localStyles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: 16,
        borderBottomWidth: 1,
        borderBottomColor: '#e0e0e0',
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: '600',
        color: '#333',
    },
    content: {
        flex: 1,
        padding: 16,
    },
    locationDisplay: {
        backgroundColor: '#f0f7ff',
        padding: 12,
        borderRadius: 8,
        marginBottom: 16,
    },
    locationText: {
        fontSize: 14,
        color: '#1D3557',
        fontWeight: '500',
    },
    section: {
        marginBottom: 20,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        color: '#333',
        marginBottom: 8,
    },
    dropdown: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#ddd',
        borderRadius: 8,
        paddingRight: 12,
    },
    dropdownInput: {
        flex: 1,
        padding: 12,
        fontSize: 15,
    },
    dropdownList: {
        marginTop: 4,
        borderWidth: 1,
        borderColor: '#ddd',
        borderRadius: 8,
        backgroundColor: '#fff',
        maxHeight: 200,
    },
    dropdownItem: {
        padding: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f0f0f0',
    },
    dropdownItemText: {
        fontSize: 15,
        color: '#333',
    },
    input: {
        borderWidth: 1,
        borderColor: '#ddd',
        borderRadius: 8,
        padding: 12,
        fontSize: 15,
        minHeight: 60,
        textAlignVertical: 'top',
    },
    suggestions: {
        marginTop: 8,
        borderWidth: 1,
        borderColor: '#ddd',
        borderRadius: 8,
        backgroundColor: '#fff',
    },
    suggestionItem: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f0f0f0',
    },
    suggestionText: {
        fontSize: 14,
        color: '#333',
        marginLeft: 8,
        flex: 1,
    },
    locationButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#1D3557',
        padding: 14,
        borderRadius: 8,
        marginBottom: 20,
    },
    locationButtonText: {
        color: '#fff',
        fontSize: 15,
        fontWeight: '600',
        marginLeft: 8,
    },
    mapContainer: {
        marginBottom: 16,
    },
    mapLabel: {
        fontSize: 13,
        color: '#666',
        marginBottom: 8,
        fontStyle: 'italic',
    },
    coordsDisplay: {
        backgroundColor: '#f5f5f5',
        padding: 8,
        borderRadius: 6,
        marginBottom: 16,
    },
    coordsText: {
        fontSize: 12,
        color: '#666',
        textAlign: 'center',
    },
    footer: {
        padding: 16,
        borderTopWidth: 1,
        borderTopColor: '#e0e0e0',
    },
    confirmButton: {
        backgroundColor: '#1D3557',
        padding: 16,
        borderRadius: 8,
        alignItems: 'center',
    },
    confirmButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '600',
    },
});

export default EnhancedLocationPicker;
