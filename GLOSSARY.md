# laravel-br-fields

The Laravel surface for [br-fields](https://github.com/singraworks/br-fields): rules that validate Brazilian documents inside the framework's validator, and casts that move them between an Eloquent model and its table. The documents themselves, and the words Mask, Format, Redact and Scrub, are defined in that package's glossary and not repeated here.

## Language

### Validation

**Rule**:
A class implementing Laravel's `ValidationRule`, judging one attribute and naming the reason it failed. `Cpf`, `Cnpj`, `CpfOrCnpj`.
_Avoid_: validator (that is the framework object holding many rules)

**String rule**:
The same judgement written as a word in a pipe-delimited rule list: `'required|cpf'`. Reports one generic message, because the framework offers no channel for a reason there.
_Avoid_: alias, shorthand

**Reason**:
The core package's `Reason` enum, carried by `InvalidDocument`. A rule maps each reason to one translation line.

**Line**:
One translated message, keyed under `br-fields::validation.<document>.<reason>`. The `invalid` line of a document is the generic one, for failures that have no reason, such as a value that is not a string.
_Avoid_: message (ambiguous with the framework's message bag), string

### Persistence

**Cast**:
A class implementing `CastsAttributes` that turns a stored column into a value object on read and a value object or string into the stored form on write. `AsCpf`, `AsCnpj`.
_Avoid_: mutator, accessor, transformer

**Stored form**:
What sits in the column: the bare characters, unpunctuated, uppercase for CNPJ. Never the formatted or redacted rendering.
_Avoid_: raw value, database value
