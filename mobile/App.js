import { useState, useEffect, useCallback } from 'react';
import {
  StyleSheet, View, Text, ScrollView, Switch,
  RefreshControl, StatusBar, SafeAreaView,
  ActivityIndicator, TouchableOpacity, TextInput,
  Platform, Alert
} from 'react-native';

const API_BASE = 'http://192.168.1.10:8000';

export default function App() {
  const [data, setData]           = useState(null);
  const [loading, setLoading]     = useState(true);
  const [error, setError]         = useState(null);
  const [refreshing, setRefreshing] = useState(false);
  const [threshold, setThreshold] = useState('35');
  const [armed, setArmed]         = useState(false);
  const [saving, setSaving]       = useState(false);

  const fetchData = useCallback(async () => {
    try {
      const [liveRes, settingsRes] = await Promise.all([
        fetch(`${API_BASE}/dashboard/live`),
        fetch(`${API_BASE}/api/settings`),
      ]);
      const live     = await liveRes.json();
      const settings = await settingsRes.json();
      setData(live);
      setThreshold(String(settings.temp_threshold));
      setArmed(settings.system_armed);
      setError(null);
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 2000);
    return () => clearInterval(interval);
  }, [fetchData]);

  const saveSettings = async () => {
    setSaving(true);
    try {
      await fetch(`${API_BASE}/api/settings`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({ temp_threshold: parseFloat(threshold), system_armed: armed }),
      });
      Alert.alert('Saved', 'Settings updated successfully.');
    } catch (e) {
      Alert.alert('Error', 'Failed to save settings.');
    } finally {
      setSaving(false);
    }
  };

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchData();
  }, [fetchData]);

  if (loading) return (
    <View style={styles.centered}>
      <ActivityIndicator size="large" color="#14b8a6"/>
      <Text style={styles.loadingText}>Connecting...</Text>
    </View>
  );

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor="#0a0a0a"/>

      <View style={styles.navbar}>
        <Text style={styles.navTitle}>🌡️ Heat Safety Monitor</Text>
        <View style={[styles.dot, { backgroundColor: error ? '#ef4444' : '#14b8a6' }]}/>
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#14b8a6"/>}
      >

        {/* Alert Banner */}
        {data?.buzzer && (
          <View style={styles.alertBanner}>
            <Text style={styles.alertIcon}>🚨</Text>
            <View>
              <Text style={styles.alertTitle}>ALERT: Heat + Occupancy</Text>
              <Text style={styles.alertSub}>Buzzer is active</Text>
            </View>
          </View>
        )}

        {error && (
          <View style={styles.errorBanner}>
            <Text style={styles.errorText}>⚠️ Cannot reach server: {error}</Text>
          </View>
        )}

        {/* Status Cards */}
        <View style={styles.grid}>
          <View style={[styles.card, armed && styles.cardTeal]}>
            <Text style={styles.cardLabel}>SYSTEM</Text>
            <Text style={[styles.cardValue, { color: armed ? '#14b8a6' : '#6b7280' }]}>
              {armed ? 'ARMED' : 'DISARMED'}
            </Text>
            <Text style={styles.cardSub}>Threshold: {data?.threshold}°C</Text>
          </View>

          <View style={[styles.card, data?.temp_high && styles.cardRed]}>
            <Text style={styles.cardLabel}>TEMPERATURE</Text>
            <Text style={[styles.cardValue, { color: data?.temp_high ? '#f87171' : '#ffffff' }]}>
              {data?.temp !== null ? `${data.temp}°C` : 'N/A'}
            </Text>
            <Text style={styles.cardSub}>{data?.temp_high ? 'Above threshold' : 'Normal'}</Text>
          </View>

          <View style={styles.card}>
            <Text style={styles.cardLabel}>HUMIDITY</Text>
            <Text style={[styles.cardValue, { color: '#ffffff' }]}>
              {data?.humidity !== null ? `${data.humidity}%` : 'N/A'}
            </Text>
            <Text style={styles.cardSub}>Relative humidity</Text>
          </View>

          <View style={[styles.card, data?.motion && styles.cardYellow]}>
            <Text style={styles.cardLabel}>MOTION</Text>
            <Text style={[styles.cardValue, { color: data?.motion ? '#fbbf24' : '#6b7280' }]}>
              {data?.motion ? 'DETECTED' : 'CLEAR'}
            </Text>
            <Text style={styles.cardSub}>{data?.motion_ago ?? '—'}</Text>
          </View>

          <View style={[styles.card, data?.buzzer && styles.cardRed]}>
            <Text style={styles.cardLabel}>BUZZER</Text>
            <Text style={[styles.cardValue, { color: data?.buzzer ? '#f87171' : '#6b7280' }]}>
              {data?.buzzer ? 'ACTIVE' : 'SILENT'}
            </Text>
            <Text style={styles.cardSub}>Auto-controlled</Text>
          </View>
        </View>

        {/* Settings Panel */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>System Settings</Text>

          <View style={styles.settingsRow}>
            <Text style={styles.settingsLabel}>Temp Threshold (°C)</Text>
            <TextInput
              style={styles.input}
              value={threshold}
              onChangeText={setThreshold}
              keyboardType="numeric"
              placeholderTextColor="#6b7280"
            />
          </View>

          <View style={styles.settingsRow}>
            <Text style={styles.settingsLabel}>Arm System</Text>
            <Switch
              value={armed}
              onValueChange={setArmed}
              trackColor={{ false: '#374151', true: '#0d9488' }}
              thumbColor={armed ? '#14b8a6' : '#6b7280'}
            />
          </View>

          <TouchableOpacity
            style={[styles.saveBtn, saving && { opacity: 0.6 }]}
            onPress={saveSettings}
            disabled={saving}
          >
            <Text style={styles.saveBtnText}>{saving ? 'Saving...' : 'Save Settings'}</Text>
          </TouchableOpacity>
        </View>

        {/* Logs */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Recent Sensor Logs</Text>
          <LogTable/>
        </View>

      </ScrollView>
    </SafeAreaView>
  );
}

function LogTable() {
  const [logs, setLogs] = useState([]);

  useEffect(() => {
    const fetchLogs = async () => {
      try {
        const res  = await fetch(`${API_BASE}/api/sensors`);
        const json = await res.json();
        const merged = [
          ...(json.dht11_temp     || []),
          ...(json.dht11_humidity || []),
          ...(json.pir            || []),
        ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
         .slice(0, 20);
        setLogs(merged);
      } catch (e) {
        console.error('Log fetch failed:', e);
      }
    };
    fetchLogs();
    const interval = setInterval(fetchLogs, 5000);
    return () => clearInterval(interval);
  }, []);

  return (
    <View>
      <View style={styles.tableHeader}>
        <Text style={[styles.tableCell, styles.tableHead, { flex: 1.5 }]}>Sensor</Text>
        <Text style={[styles.tableCell, styles.tableHead]}>Value</Text>
        <Text style={[styles.tableCell, styles.tableHead]}>Unit</Text>
        <Text style={[styles.tableCell, styles.tableHead, { flex: 1.5 }]}>Time</Text>
      </View>
      {logs.length === 0 && (
        <Text style={{ color: '#6b7280', fontSize: 12, marginTop: 8 }}>No logs yet...</Text>
      )}
      {logs.map(log => (
        <View key={log.id} style={styles.tableRow}>
          <Text style={[styles.tableCell, styles.sensorTag, { flex: 1.5 }]}>
            {log.sensor_type.toUpperCase()}
          </Text>
          <Text style={[styles.tableCell, { color: '#fff' }]}>
            {log.value ?? (log.triggered !== null ? (log.triggered ? 'YES' : 'NO') : '—')}
          </Text>
          <Text style={[styles.tableCell, { color: '#6b7280' }]}>
            {log.unit ?? '—'}
          </Text>
          <Text style={[styles.tableCell, { color: '#6b7280', flex: 1.5 }]}>
            {new Date(log.created_at).toLocaleTimeString()}
          </Text>
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  safe:         { flex: 1, backgroundColor: '#0a0a0a',
                  paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0 },
  centered:     { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#0a0a0a' },
  loadingText:  { color: '#6b7280', marginTop: 12, fontSize: 14 },
  navbar:       { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
                  paddingHorizontal: 16, paddingVertical: 14, backgroundColor: '#111827',
                  borderBottomWidth: 1, borderBottomColor: '#1f2937' },
  navTitle:     { color: '#14b8a6', fontWeight: 'bold', fontSize: 16 },
  dot:          { width: 8, height: 8, borderRadius: 4 },
  scroll:       { padding: 16, paddingBottom: 40 },
  alertBanner:  { flexDirection: 'row', alignItems: 'center', gap: 12,
                  backgroundColor: 'rgba(127,29,29,0.5)', borderWidth: 1,
                  borderColor: '#ef4444', borderRadius: 16, padding: 16, marginBottom: 12 },
  alertIcon:    { fontSize: 28 },
  alertTitle:   { color: '#fca5a5', fontWeight: 'bold', fontSize: 15 },
  alertSub:     { color: '#f87171', fontSize: 12, marginTop: 2 },
  errorBanner:  { backgroundColor: 'rgba(120,53,15,0.4)', borderWidth: 1,
                  borderColor: '#f59e0b', borderRadius: 12, padding: 12, marginBottom: 12 },
  errorText:    { color: '#fcd34d', fontSize: 13 },
  grid:         { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 16 },
  card:         { width: '47%', backgroundColor: '#111827', borderWidth: 1,
                  borderColor: '#374151', borderRadius: 16, padding: 16 },
  cardTeal:     { borderColor: '#0d9488' },
  cardRed:      { borderColor: '#ef4444' },
  cardYellow:   { borderColor: '#f59e0b' },
  cardLabel:    { color: '#6b7280', fontSize: 10, letterSpacing: 1.5, marginBottom: 6 },
  cardValue:    { fontSize: 22, fontWeight: 'bold', marginBottom: 4 },
  cardSub:      { color: '#4b5563', fontSize: 11 },
  section:      { backgroundColor: '#111827', borderWidth: 1, borderColor: '#1f2937',
                  borderRadius: 16, padding: 16, marginBottom: 16 },
  sectionTitle: { color: '#6b7280', fontSize: 12, letterSpacing: 1.5,
                  textTransform: 'uppercase', marginBottom: 12 },
  settingsRow:  { flexDirection: 'row', justifyContent: 'space-between',
                  alignItems: 'center', marginBottom: 16 },
  settingsLabel:{ color: '#d1d5db', fontSize: 14 },
  input:        { backgroundColor: '#1f2937', borderWidth: 1, borderColor: '#374151',
                  borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8,
                  color: '#ffffff', fontSize: 14, width: 80, textAlign: 'center' },
  saveBtn:      { backgroundColor: '#0d9488', borderRadius: 10,
                  paddingVertical: 12, alignItems: 'center', marginTop: 4 },
  saveBtnText:  { color: '#ffffff', fontWeight: 'bold', fontSize: 14 },
  tableHeader:  { flexDirection: 'row', borderBottomWidth: 1,
                  borderBottomColor: '#1f2937', paddingBottom: 8, marginBottom: 4 },
  tableRow:     { flexDirection: 'row', paddingVertical: 8,
                  borderBottomWidth: 1, borderBottomColor: '#1f2937' },
  tableCell:    { flex: 1, fontSize: 12, color: '#9ca3af' },
  tableHead:    { color: '#6b7280', fontSize: 11 },
  sensorTag:    { color: '#14b8a6', fontWeight: '600' },
});