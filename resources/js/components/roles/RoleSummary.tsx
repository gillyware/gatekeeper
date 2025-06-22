import { Role } from '@/types/models';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { CheckCircle, Info, PauseCircle } from 'lucide-react';

interface RoleSummaryProps {
    role: Role;
}

export default function RoleSummary({ role }: RoleSummaryProps) {
    return (
        <Card className="flex flex-col gap-4">
            <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle className="text-md font-medium">Role Details</CardTitle>
                <Info />
            </CardHeader>
            <CardContent className="flex-1 gap-2">
                <div className="flex flex-row items-center justify-start gap-2">
                    <span className="min-w-[60px] font-bold">Name:</span>
                    <span>{role.name}</span>
                </div>
                <div className="flex flex-row items-center justify-start gap-2">
                    <span className="min-w-[60px] font-bold">Status:</span>
                    <div className="flex items-center justify-start gap-2">
                        {role.is_active ? (
                            <>
                                <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                <span className="text-green-700 dark:text-green-300">Active</span>
                            </>
                        ) : (
                            <>
                                <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                <span className="text-yellow-700 dark:text-yellow-300">Inactive</span>
                            </>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
