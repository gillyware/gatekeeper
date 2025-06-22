import { useApi } from '@/lib/api';
import { StoreRoleResponse, UpdateRoleResponse } from '@/types/api/role';
import { Role } from '@/types/models';
import { Button } from '@components/ui/button';
import { Card, CardContent } from '@components/ui/card';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';
import { Transition } from '@headlessui/react';
import { useState } from 'react';

interface RoleFormProps {
    action: 'create' | 'update';
    role?: Role;
    updateRole?: (newRole: Role) => void;
}

export default function RoleForm({ role, action, updateRole }: RoleFormProps) {
    const api = useApi();
    const [name, setName] = useState<string>(role?.name || '');
    const [processing, setProcessing] = useState<boolean>(false);
    const [recentlySuccessful, setRecentlySuccessful] = useState<boolean>(false);
    const [errors, setErrors] = useState<{ name?: string; general?: string }>({});

    const submit = async (): Promise<StoreRoleResponse | UpdateRoleResponse> => {
        if (action === 'create') {
            return api.storeRole({ name: name.trim() }) as Promise<StoreRoleResponse>;
        } else {
            return api.updateRole({ id: Number(role?.id), name: name.trim() }) as Promise<UpdateRoleResponse>;
        }
    };

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        if (processing) return;

        event.preventDefault();
        setProcessing(true);
        setErrors({});

        const response: StoreRoleResponse | UpdateRoleResponse = await submit();

        setProcessing(false);

        if (response.status >= 400) {
            setErrors(response.errors || { general: 'An unexpected error occurred.' });
            return;
        }

        if (action === 'update' && role && updateRole) {
            updateRole({
                ...role,
                ...response.data,
            });
        }

        setRecentlySuccessful(true);
        setTimeout(() => setRecentlySuccessful(false), 2000);
    };

    return (
        <Card>
            <CardContent>
                <form onSubmit={handleSubmit}>
                    <div className="mb-2">
                        <Label htmlFor="name">Role Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            value={name}
                            onChange={(e) => {
                                setName(e.target.value);
                                setErrors({});
                            }}
                        />
                    </div>

                    {errors.name && <InputError className="mb-2" message={errors.name} />}
                    {errors.general && <InputError className="mb-2" message={errors.general} />}

                    <div className="flex items-center justify-end gap-4 pt-2">
                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">Saved</p>
                        </Transition>

                        <Button type="submit" disabled={processing}>
                            {action === 'create' ? 'Create' : 'Update'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
