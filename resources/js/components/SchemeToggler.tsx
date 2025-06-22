import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Button } from '@components/ui/button';
import { Monitor, Moon, Sun } from 'lucide-react';

export default function SchemeToggler({ className, ...props }: React.ComponentProps<typeof Button>) {
    const { appearance, updateAppearance } = useAppearance();

    const toggleScheme = () => {
        const next = appearance === 'system' ? 'dark' : appearance === 'dark' ? 'light' : 'system';

        updateAppearance(next);
    };

    const Icon = appearance === 'system' ? Monitor : appearance === 'dark' ? Moon : Sun;

    return (
        <Button variant="ghost" size="icon" className={cn('h-7 w-7', className)} onClick={toggleScheme} {...props}>
            <Icon className="h-4 w-4" strokeWidth={1.5} />
            <span className="sr-only">Switch Theme</span>
        </Button>
    );
}
