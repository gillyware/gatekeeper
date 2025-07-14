export interface AuditLogText {
    layout: AuditLogLayoutText;
    table: AuditLogTableText;
}

export interface AuditLogLayoutText {
    title: string;
    description: string;
    featureDisabledTitle: string;
    featureDisabledDescription: string;
}

export interface AuditLogTableText {
    actionColumn: string;
    dateTimeColumn: string;
    empty: string;
    errorFallback: string;
    pagination: (from: number, to: number, total: number) => string;
    previous: string;
    next: string;
}

export const auditLogText: AuditLogText = {
    layout: {
        title: 'Audit Log',
        description: 'Oversee Gatekeeper changes in your application',
        featureDisabledTitle: 'Audit Feature Disabled',
        featureDisabledDescription:
            'Gatekeeper actions are not currently being logged. To capture all actions, please enable the audit feature in your Gatekeeper configuration.',
    },
    table: {
        actionColumn: 'Action',
        dateTimeColumn: 'Date/Time',
        empty: 'No logs found.',
        errorFallback: 'Failed to load logs.',
        pagination: (from, to, total) => `${from} to ${to} of ${total}`,
        previous: 'Previous',
        next: 'Next',
    },
};
