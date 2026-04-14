import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva(
  "inline-flex items-center justify-center rounded-full border px-2.5 py-1 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-200 overflow-hidden",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90",
        success:
          "border-transparent bg-[color:var(--color-badge-green-bg-light)] text-[color:var(--color-badge-green-text)]",
        warning:
          "border-transparent bg-[color:var(--color-badge-yellow-bg-light)] text-[color:var(--color-badge-yellow-text)]",
        danger:
          "border-transparent bg-[color:var(--color-badge-red-bg-light)] text-[color:var(--color-badge-red-text)]",
        secondary:
          "border-transparent bg-[color:var(--color-badge-grey-bg-light)] text-[color:var(--color-badge-grey-text)]",
        destructive:
          "border-transparent bg-destructive text-white [a&]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "border-border bg-white text-foreground [a&]:hover:bg-gray-100",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)
export type BadgeVariants = VariantProps<typeof badgeVariants>
