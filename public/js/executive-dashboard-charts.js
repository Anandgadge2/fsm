// Executive Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    // Incident Status Chart
    if (typeof window.incidentTrackingData !== 'undefined' && document.getElementById('incidentStatusChart')) {
        const statusCtx = document.getElementById('incidentStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: window.incidentTrackingData.statusLabels,
                datasets: [{
                    data: window.incidentTrackingData.statusData,
                    backgroundColor: ['#dc3545', '#28a745', '#6c757d', '#ffc107', '#17a2b8', '#6610f2', '#e83e8c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Incident Priority Chart
    if (typeof window.incidentTrackingData !== 'undefined' && document.getElementById('incidentPriorityChart')) {
        const priorityCtx = document.getElementById('incidentPriorityChart').getContext('2d');
        new Chart(priorityCtx, {
            type: 'doughnut',
            data: {
                labels: window.incidentTrackingData.priorityLabels,
                datasets: [{
                    data: window.incidentTrackingData.priorityData,
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Incident Type Chart
    if (typeof window.incidentTrackingData !== 'undefined' && document.getElementById('incidentTypeChart')) {
        const typeCtx = document.getElementById('incidentTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: window.incidentTrackingData.typeLabels,
                datasets: [{
                    label: 'Incidents',
                    data: window.incidentTrackingData.typeData,
                    backgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Patrol Type Chart
    if (typeof window.patrolAnalyticsData !== 'undefined' && document.getElementById('patrolTypeChart')) {
        const patrolTypeCtx = document.getElementById('patrolTypeChart').getContext('2d');
        new Chart(patrolTypeCtx, {
            type: 'bar',
            data: {
                labels: window.patrolAnalyticsData.typeLabels,
                datasets: [{
                    label: 'Count',
                    data: window.patrolAnalyticsData.typeCounts,
                    backgroundColor: '#28a745'
                }, {
                    label: 'Distance (km)',
                    data: window.patrolAnalyticsData.typeDistances,
                    backgroundColor: '#17a2b8',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    y1: { beginAtZero: true, position: 'right' }
                }
            }
        });
    }

    // Daily Patrol Trend Chart
    if (typeof window.patrolAnalyticsData !== 'undefined' && document.getElementById('dailyPatrolTrendChart')) {
        const dailyTrendCtx = document.getElementById('dailyPatrolTrendChart').getContext('2d');
        new Chart(dailyTrendCtx, {
            type: 'line',
            data: {
                labels: window.patrolAnalyticsData.dailyLabels,
                datasets: [{
                    label: 'Patrol Count',
                    data: window.patrolAnalyticsData.dailyCounts,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Distance (km)',
                    data: window.patrolAnalyticsData.dailyDistances,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    y1: { beginAtZero: true, position: 'right' }
                }
            }
        });
    }

    // Attendance Trend Chart
    if (typeof window.attendanceData !== 'undefined' && document.getElementById('attendanceTrendChart')) {
        const attendanceTrendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(attendanceTrendCtx, {
            type: 'line',
            data: {
                labels: window.attendanceData.dailyLabels,
                datasets: [{
                    label: 'Present',
                    data: window.attendanceData.presentData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Absent',
                    data: window.attendanceData.absentData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Late',
                    data: window.attendanceData.lateData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Hourly Distribution Chart
    if (typeof window.attendanceData !== 'undefined' && document.getElementById('hourlyDistributionChart')) {
        const hourlyCtx = document.getElementById('hourlyDistributionChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: window.attendanceData.hourlyLabels,
                datasets: [{
                    label: 'Patrol Count',
                    data: window.attendanceData.hourlyData,
                    backgroundColor: '#17a2b8'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});

