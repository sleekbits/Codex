export type FieldType = "string" | "number" | "date" | "currency" | "longtext";

export type PlaceholderMeta = {
  key: string;
  format: string;
  slideIndex: number;
  shapeName?: string;
  context: string;
  suggestedType: FieldType;
  label?: string;
  required?: boolean;
  defaultValue?: string;
};
