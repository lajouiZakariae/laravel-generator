import { UUID } from 'https://unpkg.com/uuidjs@^5'

PetiteVue.createApp({
    tables: [
        {
            tableId: UUID.generate(),
            tableName: 'users',
            columns: [
                {
                    id: UUID.generate(),
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
            tableId: UUID.generate(),
            tableName: 'bank_developers',
            columns: [
                {
                    id: UUID.generate(),
                    name: 'id',
                    unsigned: true,
                    type: 'bigint',
                    isPrimary: true,
                    isNullable: false,
                    isForeign: false,
                    foreign: null,
                },
                {
                    id: UUID.generate(),
                    name: 'zip_code',
                    type: 'string(10)',
                    isPrimary: false,
                    isNullable: false,
                    isForeign: false,
                    foreign: null,
                },
                {
                    id: UUID.generate(),
                    name: 'status',
                    type: "enum('Accepted','Banned','Pending')",
                    isNullable: false,
                },
                {
                    id: UUID.generate(),
                    name: 'is_adult',
                    type: 'bool',
                    isNullable: false,
                },
                {
                    id: UUID.generate(),
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
    ],
    successMessages: [],
    errorMessages: [],
    addEmptyColumn(ev) {
        console.log(ev.target.dataset.tableId)

        const table = this.tables.find(
            table => table.tableId === ev.target.dataset.tableId
        )

        table.columns.push({
            id: UUID.generate(),
            name: '',
            type: '',
            isPrimary: false,
            isNullable: false,
            isForeign: false,
            foreign: null,
        })
    },
    async post() {
        try {
            const preparedTables = prepareColumnsOfTables(this.tables)

            const tables = resolveRelations(preparedTables)

            const response = await fetch('/laravelgenerator', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(tables),
            })

            const responseBody = await response.json()

            // this.successMessages = responseBody.messages;

            // this.successMessages.forEach((message) => {
            //     Toastify({
            //         text: message,
            //         duration: 3000,
            //         destination: "https://github.com/apvarun/toastify-js",
            //         newWindow: true,
            //         close: true,
            //         gravity: "bottom", // `top` or `bottom`
            //         position: "right", // `left`, `center` or `right`
            //         stopOnFocus: true, // Prevents dismissing of toast on hover
            //         style: {
            //             background:
            //                 "linear-gradient(to right, #00b09b, #96c93d)",
            //         },
            //         onClick: function () {}, // Callback after click
            //     }).showToast();
            // });
        } catch (error) {}
    },
}).mount('.form')

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

        if (type.startsWith('enum')) {
            const enumValues = type
                .replace('enum(', '')
                .replace(')', '')
                .replace(/'/g, '')
                .split(',')

            column.type = 'enum'

            column.enumValues = enumValues

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
                    localTable: table.tableName,
                }

                const hasManyRelation = {
                    type: 'has-many',
                    foreignKey: column.name,
                    foreignTable: column.foreign.on,
                    localKey: column.foreign.references,
                    localTable: table.tableName,
                }

                if (!relations.hasOwnProperty(table.tableName)) {
                    relations[table.tableName] = []
                }

                relations[table.tableName].push(belongsToRelation)

                if (!relations.hasOwnProperty(column.foreign.on)) {
                    relations[column.foreign.on] = []
                }

                relations[column.foreign.on].push(hasManyRelation)
            }
        })

        return relations
    }, {})

    tables.forEach(table => {
        const tableRelations = relations[table.tableName]

        if (tableRelations) {
            table.relations = tableRelations
        }
    })

    return tables
}
