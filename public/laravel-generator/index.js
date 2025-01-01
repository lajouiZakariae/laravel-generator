import { UUID } from 'https://unpkg.com/uuidjs@^5'

PetiteVue.createApp({
    columns: [
        {
            id: UUID.generate(),
            name: 'id',
            type: 'int',
            isPrimary: true,
            isNullable: false,
            isForeign: false,
            foreign: null,
        },
        {
            id: UUID.generate(),
            name: 'name',
            type: 'string',
            isPrimary: false,
            isNullable: false,
            isForeign: false,
            foreign: null,
        },
        {
            id: UUID.generate(),
            name: 'user_id',
            type: 'string',
            isPrimary: false,
            isNullable: false,
            isForeign: true,
            foreign: {
                references: 'id',
                on: 'users',
            },
        },
        {
            id: UUID.generate(),
            name: 'status',
            type: "enum('Accepted','Banned','Pending')",
            isPrimary: false,
            isNullable: false,
            isForeign: false,
            foreign: null,
        },
    ],
    addEmptyColumn() {
        this.columns.push({
            id: UUID.generate(),
            name: '',
            type: '',
            isPrimary: false,
            isNullable: false,
        })
    },
    async post() {
        try {
            console.log(JSON.stringify(this.columns))

            const response = await fetch('/', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.columns),
            })

            const responseBody = await response.json()

            console.log(responseBody)
        } catch (error) {}
    },
}).mount('.form')

const formData = [
    {
        table_name: 'users',
        columns: [
            {
                id: '8ceaf7bc-dfe2-401a-b7f7-ed5e68d86679',
                name: 'id',
                unsigned: true,
                type: 'bigint',
                isPrimary: true,
                isNullable: false,
                isForeign: false,
                foreign: null,
            },
        ],
    },
    {
        table_name: 'bank_developers',
        columns: [
            {
                id: '8ceaf7bc-dfe2-401a-b7f7-ed5e68d86679',
                name: 'id',
                unsigned: true,
                type: 'bigint',
                isPrimary: true,
                isNullable: false,
                isForeign: false,
                foreign: null,
            },
            {
                id: 'dea235dd-dcf7-46c7-a316-9d74aeb7a1a9',
                name: 'zip_code',
                type: 'string(10)',
                isPrimary: false,
                isNullable: false,
                isForeign: false,
                foreign: null,
            },
            {
                id: '2440dd36-5488-4a48-9f6c-731c2a6d285f',
                name: 'name',
                type: 'string',
                isPrimary: false,
                isNullable: false,
                isForeign: false,
                foreign: null,
            },
            {
                id: '2440dd36-5488-4a48-9f6c-731c2a6d285d',
                name: 'user_id',
                type: 'bigint',
                isPrimary: false,
                isNullable: false,
                isForeign: true,
                foreign: {
                    references: 'id',
                    on: 'users',
                },
            },
        ],
    },
]

const tableWithColumnsPrepared = prepareColumnsOfTables(formData)

const tableWithRelationsResolved = resolveRelations(tableWithColumnsPrepared)

console.log(JSON.stringify(tableWithRelationsResolved))

/**
 * Prepare columns of the tables
 * @param {Object[]} table
 * @return {Object[]}
 */
function prepareColumnsOfTables(table) {
    return table.map(table => prepareColumnsOfTable(table))
}

/**
 * Prepare columns of the table
 * @param {Object} table
 * @return {Object}
 */
function prepareColumnsOfTable(table) {
    const columns = table.columns

    if (!columns) {
        throw new Error('Columns are required')
    }

    const preparedColumns = columns.map(column => {
        const { type } = column

        if (type === 'string') {
            column.stringMax = 255
            return column
        }

        if (type.startsWith('string')) {
            const stringMax = type.replace('string(', '').replace(')', '')

            column.type = 'string'

            column.stringMax = stringMax

            return column
        }

        return column
    })

    table.columns = preparedColumns

    return table
}

/**
 * Resolve relations
 * @param {Object[]} tables
 * @return {Object[]}
 */
function resolveRelations(tables) {
    const relations = tables.reduce(function (relations, table) {
        const columns = table.columns

        if (!columns) {
            throw new Error('Columns are required')
        }

        columns.forEach(column => {
            const { foreign } = column

            if (foreign) {
                const belongsToRelation = {
                    type: 'belongs-to',
                    foreignKey: column.name,
                    foreignTable: column.foreign.on,
                    localKey: column.foreign.references,
                    localTable: table.table_name,
                }

                const hasManyRelation = {
                    type: 'has-many',
                    foreignKey: column.name,
                    foreignTable: column.foreign.on,
                    localKey: column.foreign.references,
                    localTable: table.table_name,
                }

                if (!relations.hasOwnProperty(table.table_name)) {
                    relations[table.table_name] = []
                }

                relations[table.table_name].push(belongsToRelation)

                if (!relations.hasOwnProperty(column.foreign.on)) {
                    relations[column.foreign.on] = []
                }

                relations[column.foreign.on].push(hasManyRelation)
            }
        })

        return relations
    }, {})

    tables.forEach(table => {
        const tableRelations = relations[table.table_name]

        if (tableRelations) {
            table.relations = tableRelations
        }
    })

    return tables
}
