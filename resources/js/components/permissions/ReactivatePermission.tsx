import { useState } from 'react';

import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';

import { useApi } from '@/lib/api';
import { Permission } from '@/types/models';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@components/ui/dialog';

interface ReactivatePermissionProps {
    permission: Permission;
    updatePermission: (newPermission: Permission) => void;
}

export default function ReactivatePermission({ permission, updatePermission }: ReactivatePermissionProps) {
    const api = useApi();
    const [permissionName, setPermissionName] = useState<string>('');
    const [processing, setProcessing] = useState<boolean>(false);
    const [errors, setErrors] = useState<{ general?: string }>({});

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        if (processing) return;

        event.preventDefault();

        if (permissionName !== permission.name) {
            setErrors({ general: 'Permission name does not match.' });
            return;
        }

        setProcessing(true);
        setErrors({});

        const response = await api.reactivatePermission({ id: permission?.id as number });

        setProcessing(false);

        if (response.status >= 400) {
            setErrors(response.errors || { general: 'An unexpected error occurred.' });
            return;
        }

        closeModal();
        updatePermission({
            ...permission,
            ...response.data,
        });
    };

    const closeModal = () => {
        setPermissionName('');
        setErrors({});
        setProcessing(false);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4 rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-200/10 dark:bg-green-700/10">
                <div className="relative space-y-0.5 text-green-600 dark:text-green-100">
                    <p className="font-medium">Reactivate Permission</p>
                    <p className="text-sm">Once reactivated, the permission will once again grant access to all models it's currently assigned to.</p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="default">Reactivate</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Are you sure you want to reactivate this permission?</DialogTitle>
                        <DialogDescription>Type "{permission.name}" to confirm reactivation of this permission.</DialogDescription>
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="sr-only">
                                    Permission Name
                                </Label>

                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={permissionName}
                                    onChange={(e) => {
                                        setPermissionName(e.target.value);
                                        setErrors({});
                                    }}
                                />

                                <InputError message={errors.general} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary" onClick={closeModal}>
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button variant="default" disabled={processing} asChild>
                                    <button type="submit">Reactivate</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
