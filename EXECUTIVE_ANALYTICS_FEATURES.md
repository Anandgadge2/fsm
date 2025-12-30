# Executive Analytics Dashboard - New Features

## Overview
A comprehensive analytics dashboard designed specifically for forest officers to monitor and analyze forest guard activities, attendance, patrolling, and incidents.

## Key Features Added

### 1. **Executive Analytics Dashboard** (`/analytics/executive`)
   - **Comprehensive KPI Cards**: 8 key metrics at a glance
     - Active Guards
     - Total Patrols (completed/ongoing)
     - Total Distance Patrolled (with average per guard)
     - Attendance Rate (with present/absent counts)
     - Total Incidents (with pending count)
     - Resolution Rate
     - Site Coverage Percentage
     - Total Sites

### 2. **Guard Performance Rankings**
   - **Top Performers**: Top 5 guards based on performance score
   - **Performance Score Calculation**: Combines:
     - Attendance days (weighted)
     - Patrol distance covered
     - Incidents reported
   - **Full Performance Table**: All guards ranked with:
     - Patrol sessions count
     - Total distance covered
     - Days present
     - Incidents reported
     - Overall performance score

### 3. **Incident Status Tracking**
   - **Status Distribution**: Visual breakdown of incident statuses:
     - Pending Supervisor
     - Resolved
     - Ignored
     - Escalated to Admin
     - Pending Admin
     - Escalated to Client
     - Reverted
   - **Priority Distribution**: High, Medium, Low priority incidents
   - **Incident Types**: Breakdown by type (animal mortality, human impact, etc.)
   - **Resolution Time Analysis**: Average and maximum days to resolve by type
   - **Critical Incidents Alert**: Recent high/medium priority incidents requiring attention
   - **Site-wise Incident Summary**: Total, resolved, and pending counts per site

### 4. **Patrol Analytics**
   - **Patrol by Type**: Routine, Special, Joint, Other
   - **Patrol by Session**: Foot, Vehicle, Bicycle
   - **Foot vs Night Patrols**: Separate counts and analysis
   - **Daily Patrol Trend**: Line chart showing patrol count and distance over time
   - **Distance by Site**: Top sites by patrol distance covered

### 5. **Attendance Analytics**
   - **Daily Attendance Trend**: Present, absent, and late trends over time
   - **Late Attendance Analysis**: 
     - Guards with most late arrivals
     - Average late minutes per guard
   - **Attendance by Site**: Present count and total guards per site

### 6. **Risk Zone Analysis**
   - **High Incident Zones**: Locations with multiple incidents (animal mortality, human impact)
   - **Coverage Gaps**: Sites with no patrols in the selected period
   - **Most Patrolled Sites**: Sites with highest patrol activity

### 7. **Time-based Patterns**
   - **Hourly Distribution**: Patrol count by hour of day
   - **Peak Patrol Hours**: Top 5 hours with most patrols
   - **Day of Week Analysis**: Patrol patterns by weekday

### 8. **Efficiency Metrics**
   - **Average Patrol Duration**: Hours per patrol
   - **Average Speed**: Kilometers per hour
   - **Completion Rate**: Percentage of started patrols that completed
   - **Guard Efficiency Table**: Individual guard metrics:
     - Session count
     - Total distance
     - Average distance per session
     - Average duration

### 9. **Coverage Analysis**
   - **Site Coverage Percentage**: Percentage of sites with patrol coverage
   - **Sites Most Patrolled**: Top sites by patrol count
   - **Sites Least Patrolled**: Sites needing more attention

## Technical Implementation

### Controller
- **File**: `app/Http/Controllers/ExecutiveAnalyticsController.php`
- **Methods**: 
  - `executiveDashboard()`: Main entry point
  - `getKPIs()`: Key performance indicators
  - `getGuardPerformanceRankings()`: Guard performance analysis
  - `getAttendanceAnalytics()`: Attendance metrics with late analysis
  - `getPatrolAnalytics()`: Comprehensive patrol statistics
  - `getIncidentStatusTracking()`: Incident status and resolution tracking
  - `getRiskZoneAnalysis()`: High-risk areas and coverage gaps
  - `getTimeBasedPatterns()`: Time-based patrol patterns
  - `getCoverageAnalysis()`: Site coverage statistics
  - `getEfficiencyMetrics()`: Patrol efficiency metrics

### Views
- **Main View**: `resources/views/analytics/executive-dashboard.blade.php`
- **Partials**: 
  - `resources/views/analytics/partials/kpi-cards.blade.php`
  - `resources/views/analytics/partials/guard-performance.blade.php`
  - `resources/views/analytics/partials/incident-tracking.blade.php`
  - `resources/views/analytics/partials/patrol-analytics.blade.php`
  - `resources/views/analytics/partials/attendance-analytics.blade.php`
  - `resources/views/analytics/partials/risk-coverage.blade.php`
  - `resources/views/analytics/partials/efficiency-metrics.blade.php`

### JavaScript
- **File**: `public/js/executive-dashboard-charts.js`
- **Library**: Chart.js 3.9.1
- **Charts**: 
  - Incident status doughnut chart
  - Incident priority doughnut chart
  - Incident type bar chart
  - Patrol type bar chart
  - Daily patrol trend line chart
  - Attendance trend line chart
  - Hourly distribution bar chart

### Routes
- **Route**: `/analytics/executive`
- **Name**: `analytics.executive`
- **Added to**: Main dashboard navigation tiles

## Database Tables Used

1. **users**: Guard/user information
2. **attendance**: Attendance records with late time tracking
3. **patrol_sessions**: Patrol session data with distance and duration
4. **patrol_logs**: Patrol log entries (incidents, sightings, etc.)
5. **incidence_details**: Detailed incident records with status and priority
6. **site_details**: Site/beat information
7. **site_geofences**: Geofence definitions
8. **client_details**: Client/range information
9. **site_assign**: Guard-to-site assignments

## Filter Support

The dashboard supports filtering by:
- **Date Range**: Start date and end date
- **Range**: Client/range filter
- **Beat**: Site/beat filter
- **Compartment**: Geofence/compartment filter

Filters are applied using the `FilterDataTrait` which resolves hierarchical filters (Range → Beat → Compartment) into site IDs.

## Usage

1. Navigate to the main dashboard
2. Click on "Executive Analytics" tile
3. Select date range and optional filters (Range, Beat, Compartment)
4. View comprehensive analytics and insights

## Benefits for Forest Officers

1. **Quick Overview**: All key metrics on one screen
2. **Performance Monitoring**: Identify top performers and areas needing improvement
3. **Incident Management**: Track incident statuses and resolution times
4. **Resource Allocation**: Identify coverage gaps and high-risk zones
5. **Efficiency Analysis**: Monitor patrol efficiency and completion rates
6. **Trend Analysis**: Understand patterns in patrol and attendance
7. **Decision Support**: Data-driven insights for better resource management

