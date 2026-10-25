export const notificationStorageKey = 'milestone.notifications.shared.v1';

export const previewNotifications = {
    student: [
        { id: 'student-deadline', title: 'M2 is due in 3 days', time: '12 minutes ago' },
        { id: 'student-update', title: 'Professor Lee posted an announcement', time: 'Yesterday' },
        { id: 'student-risk', title: 'Risk status changed to Medium', time: 'Yesterday' },
    ],
    ta: [
        { id: 'ta-submission', title: 'Team 3 submitted M2 Design Document', time: '12 minutes ago' },
        { id: 'ta-issue', title: 'Team 3 reported a project issue', time: 'Yesterday' },
        { id: 'ta-invitation', title: 'Invitation to Database Systems', time: 'Monday' },
    ],
    professor: [
        { id: 'prof-submission', title: 'Team 3 submitted an M2 Design Document', time: '12 minutes ago' },
        { id: 'prof-review', title: 'A submission review was completed', time: '2 hours ago' },
        { id: 'prof-progress', title: 'Project progress was updated', time: 'Yesterday' },
    ],
    admin: [
        { id: 'admin-registration', title: '12 new student registrations', time: '18 minutes ago' },
        { id: 'admin-alert', title: 'Storage usage is above 80%', time: '1 hour ago' },
        { id: 'admin-course', title: 'CS 342 course details were updated', time: 'Yesterday' },
    ],
};
