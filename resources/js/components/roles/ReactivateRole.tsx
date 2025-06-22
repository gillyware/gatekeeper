import { useState } from 'react';

import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';

import { useApi } from '@/lib/api';
import { Role } from '@/types/models';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@components/ui/dialog';

interface ReactivateRoleProps {
    role: Role;
    updateRole: (newRole: Role) => void;
}

export default function ReactivateRole({ role, updateRole }: ReactivateRoleProps) {
    const api = useApi();
    const [roleName, setRoleName] = useState<string>('');
    const [processing, setProcessing] = useState<boolean>(false);
    const [errors, setErrors] = useState<{ general?: string }>({});

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        if (processing) return;

        event.preventDefault();

        if (roleName !== role.name) {
            setErrors({ general: 'Role name does not match.' });
            return;
        }

        setProcessing(true);
        setErrors({});

        const response = await api.reactivateRole({ id: role?.id as number });

        setProcessing(false);

        if (response.status >= 400) {
            setErrors(response.errors || { general: 'An unexpected error occurred.' });
            return;
        }

        closeModal();
        updateRole({
            ...role,
            ...response.data,
        });
    };

    const closeModal = () => {
        setRoleName('');
        setErrors({});
        setProcessing(false);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4 rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-200/10 dark:bg-green-700/10">
                <div className="relative space-y-0.5 text-green-600 dark:text-green-100">
                    <p className="font-medium">Reactivate Role</p>
                    <p className="text-sm">Once reactivated, the role will once again grant its assigned permissions to all associated models.</p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="default">Reactivate</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Are you sure you want to reactivate this role?</DialogTitle>
                        <DialogDescription>Type "{role.name}" to confirm reactivation of this role.</DialogDescription>
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="sr-only">
                                    Role Name
                                </Label>

                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={roleName}
                                    onChange={(e) => {
                                        setRoleName(e.target.value);
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
