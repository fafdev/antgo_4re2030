export type ContactType = 'Persona' | 'Empresa';

const NIF_CONTROL_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';
const CIF_CONTROL_LETTERS = 'JABCDEFGHI';

export function normalizeSpanishTaxId(rawTaxId: string): string {
    return rawTaxId.trim().toUpperCase().replace(/[\s-]/g, '');
}

export function detectSpanishTaxIdType(rawTaxId: string): ContactType | null {
    const taxId = normalizeSpanishTaxId(rawTaxId);

    if (isValidCif(taxId)) {
        return 'Empresa';
    }

    if (isValidNif(taxId) || isValidNie(taxId)) {
        return 'Persona';
    }

    return null;
}

function isValidNif(taxId: string): boolean {
    if (!/^\d{8}[A-Z]$/.test(taxId)) {
        return false;
    }

    const number = Number.parseInt(taxId.slice(0, 8), 10);
    const expectedLetter = NIF_CONTROL_LETTERS[number % 23];

    return taxId[8] === expectedLetter;
}

function isValidNie(taxId: string): boolean {
    if (!/^[XYZ]\d{7}[A-Z]$/.test(taxId)) {
        return false;
    }

    const replacements: Record<string, string> = {
        X: '0',
        Y: '1',
        Z: '2',
    };

    const numericNie = `${replacements[taxId[0]]}${taxId.slice(1, 8)}`;
    const number = Number.parseInt(numericNie, 10);
    const expectedLetter = NIF_CONTROL_LETTERS[number % 23];

    return taxId[8] === expectedLetter;
}

function isValidCif(taxId: string): boolean {
    if (!/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/.test(taxId)) {
        return false;
    }

    const entityType = taxId[0];
    const controlCharacter = taxId[8];
    const digits = taxId.slice(1, 8);

    let oddPositionSum = 0;
    let evenPositionSum = 0;

    for (let index = 0; index < digits.length; index += 1) {
        const digit = Number.parseInt(digits[index], 10);

        if (index % 2 === 0) {
            const doubled = digit * 2;
            oddPositionSum += Math.floor(doubled / 10) + (doubled % 10);
        } else {
            evenPositionSum += digit;
        }
    }

    const controlDigit = (10 - ((oddPositionSum + evenPositionSum) % 10)) % 10;
    const controlLetter = CIF_CONTROL_LETTERS[controlDigit];

    if (['A', 'B', 'E', 'H'].includes(entityType)) {
        return controlCharacter === String(controlDigit);
    }

    if (['K', 'P', 'Q', 'S'].includes(entityType)) {
        return controlCharacter === controlLetter;
    }

    return controlCharacter === String(controlDigit) || controlCharacter === controlLetter;
}
