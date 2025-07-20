import AppLogoIcon from '@components/app/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md text-[#333872] dark:text-white">
                <AppLogoIcon className="size-6" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-none font-semibold text-neutral-900 dark:text-neutral-100">Gatekeeper</span>
            </div>
        </>
    );
}
