import { Input } from '@components/ui/input';
import { useEffect, useRef } from 'react';

interface DebouncedInputProps {
    value: string;
    debounceTime?: number;
    placeholder?: string;
    setValue: (value: string) => void;
    onDebouncedChange: (value: string) => Promise<void>;
}

export function DebouncedInput({ value, debounceTime = 500, placeholder, setValue, onDebouncedChange }: DebouncedInputProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const timeoutRef = useRef<NodeJS.Timeout>(undefined);
    const requestInProgressRef = useRef<boolean>(false);

    useEffect(() => () => clearTimeout(timeoutRef.current), []);

    return (
        <Input
            ref={inputRef}
            type="text"
            placeholder={placeholder}
            autoComplete="off"
            autoCorrect="off"
            spellCheck="false"
            autoCapitalize="off"
            value={value}
            onChange={(e) => {
                const newValue = e.target.value;
                setValue(newValue);

                if (timeoutRef.current) {
                    clearTimeout(timeoutRef.current);
                }

                timeoutRef.current = setTimeout(async () => {
                    if (requestInProgressRef.current) return;

                    requestInProgressRef.current = true;
                    await onDebouncedChange(newValue);
                    requestInProgressRef.current = false;
                }, debounceTime);
            }}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    if (requestInProgressRef.current) return;

                    if (timeoutRef.current) {
                        clearTimeout(timeoutRef.current);
                    }

                    requestInProgressRef.current = true;
                    onDebouncedChange(e.currentTarget.value);
                    requestInProgressRef.current = false;
                }
            }}
        />
    );
}
