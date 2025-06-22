import { useState } from 'react';

import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';

import { useApi } from '@/lib/api';
import { Role } from '@/types/models';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@components/ui/dialog';
import { useNavigate } from 'react-router';

interface DeleteRoleProps {
    role: Role;
}

export default function DeleteRole({ role }: DeleteRoleProps) {
    const api = useApi();
    const navigate = useNavigate();

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

        const response = await api.deleteRole({ id: role?.id as number });

        setProcessing(false);

        if (response.status >= 400) {
            setErrors(response.errors || { general: 'An unexpected error occurred.' });
            return;
        }

        closeModal();
        navigate('/roles', { replace: true });
    };

    const closeModal = () => {
        setRoleName('');
        setErrors({});
        setProcessing(false);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">Delete Role</p>
                    <p className="text-sm">This role will be removed from the application.</p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="destructive">Delete</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Are you sure you want to delete this role?</DialogTitle>
                        <DialogDescription>Type "{role.name}" to confirm deletion of this role.</DialogDescription>
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

                                <Button variant="destructive" disabled={processing} asChild>
                                    <button type="submit">Delete</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
